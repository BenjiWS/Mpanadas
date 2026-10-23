<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\DescribesRoleAbilities;
use App\Livewire\Concerns\FormatsShortDates;
use App\Livewire\Concerns\ReportsErrors;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class CrudUser extends Component
{
    use AuthorizesPermissions, DescribesRoleAbilities, FormatsShortDates, ReportsErrors;

    #[Locked]
    public $action = 'store';

    public $title = 'Nuevo usuario';

    public $name;

    public $lastname;

    public $username;

    public $email;

    public $password;

    public $role_id;

    public $is_active = true;

    #[Locked]
    public $user_id;

    protected function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'regex:/^[A-Za-z0-9._-]+$/', 'max:50', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => $this->roleRules(),
            'is_active' => ['boolean'],
        ];

        if ($this->action === 'edit') {
            $rules['username'] = ['required', 'string', 'regex:/^[A-Za-z0-9._-]+$/', 'max:50', Rule::unique('users', 'username')->ignore($this->user_id)];
            $rules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user_id)];
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        return $rules;
    }

    /**
     * El select de rol solo se muestra con 'assign roles', pero role_id se puede enviar igual,
     * así que aquí se valida: rol existente, Administrador solo lo asigna otro Administrador.
     */
    protected function roleRules(): array
    {
        if ($this->action === 'edit' && ! $this->assignsRole()) {
            return ['nullable'];
        }

        $roles = Rule::exists('roles', 'id');

        if (! auth()->user()->hasRole('Administrador')) {
            $roles->whereNot('name', 'Administrador');
        }

        return ['required', $roles, function (string $attribute, mixed $value, \Closure $fail) {
            if (! auth()->user()->can('assign roles')) {
                $fail(__('No tienes permiso para asignar roles.'));
            }
        }];
    }

    /**
     * Al editar, el rol solo cambia si se tiene el permiso y no es el propio usuario.
     */
    protected function assignsRole(): bool
    {
        return auth()->user()->can('assign roles') && ! $this->isSelf();
    }

    protected function isSelf(): bool
    {
        return $this->action === 'edit' && (int) $this->user_id === auth()->id();
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre'),
            'lastname.required' => __('Debe ingresar el apellido'),
            'username.required' => __('Debe ingresar el username'),
            'username.regex' => __('El username solo puede tener letras, números, puntos, guiones y guiones bajos'),
            'username.unique' => __('El username ya ha sido registrado'),
            'email.required' => __('Debe ingresar el correo'),
            'email.email' => __('Ingrese un correo válido'),
            'email.unique' => __('El correo ya ha sido registrado'),
            'password.required' => __('Debe ingresar una contraseña'),
            'password.min' => __('La contraseña debe tener al menos 8 caracteres'),
            'role_id.required' => __('Debe seleccionar un rol'),
            'role_id.exists' => __('Seleccione un rol válido'),
        ];
    }

    public function mount(User $user)
    {
        if ($user->exists) {
            abort_if(UsersTable::cannotEdit($user), 403);

            $this->action = 'edit';
            $this->user_id = $user->id;
            $this->name = $user->name;
            $this->lastname = $user->lastname;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->password = null;
            $this->role_id = $user->roles->first()?->id;
            $this->is_active = $user->is_active ?? true;
            $this->title = __('Edición de :name', ['name' => $user->name]);
        }
    }

    public function render()
    {
        $roles = auth()->user()->hasRole('Administrador')
            ? Role::orderBy('name')->get()
            : Role::where('name', '!=', 'Administrador')->orderBy('name')->get();

        $user = $this->user_id ? User::find($this->user_id) : null;

        return view('livewire.users.crud-user', [
            'roles' => $roles,
            'isSelf' => $this->isSelf(),
            // Lo que puede hacer con el rol elegido (se actualiza al cambiar el rol).
            'abilities' => $this->roleAbilities($this->role_id ? Role::with('permissions')->find($this->role_id) : null),
            'createdAt' => $this->shortDate($user?->created_at),
            'updatedAt' => $this->shortDate($user?->updated_at),
        ])->title($this->title);
    }

    public function store()
    {
        $this->authorizePermission('create users');

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $data['name'],
                'lastname' => $data['lastname'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'],
            ]);
            $user->syncRoles(Role::findById($data['role_id'], 'web'));

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Usuario creado con éxito'),
                redirect: route('users'),
            );

        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('swalmodal',
                type: 'warning',
                title: __('¡Error!'),
                text: $this->errorMessage($e),
            );
        }
    }

    public function update()
    {
        $this->authorizePermission('edit users');

        // Se usa el usuario que se abrió en mount(), no un id enviado desde el navegador.
        $user = User::findOrFail($this->user_id);
        abort_if(UsersTable::cannotEdit($user), 403);

        $data = $this->validate();

        try {
            DB::beginTransaction();

            $userData = [
                'name' => $data['name'],
                'lastname' => $data['lastname'],
                'username' => $data['username'],
                'email' => $data['email'],
                // Uno mismo no se puede desactivar: se quedaría fuera del sistema.
                'is_active' => $this->isSelf() ? true : $data['is_active'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $user->update($userData);

            if ($this->assignsRole()) {
                $user->syncRoles(Role::findById($data['role_id'], 'web'));
            }

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('Actualizado con éxito'),
                redirect: route('users'),
            );

        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('swalmodal',
                type: 'warning',
                title: __('¡Error!'),
                text: $this->errorMessage($e),
            );
        }
    }
}
