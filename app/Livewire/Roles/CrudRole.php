<?php

namespace App\Livewire\Roles;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CrudRole extends Component
{
    use AuthorizesPermissions, ReportsErrors;

    #[Locked]
    public $action = 'store';

    public $title = 'Nuevo rol';

    public $name;

    public $description;

    public $permission_id = [];

    #[Locked]
    public $role_id;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->role_id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_id' => ['nullable', 'array'],
            'permission_id.*' => ['integer', Rule::exists('permissions', 'id')],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('Debe ingresar el nombre del rol'),
            'name.unique' => __('El rol ya ha sido registrado'),
            'description.max' => __('La descripción no puede pasar de 255 caracteres'),
        ];
    }

    public function mount(Role $role)
    {
        if ($role->exists) {
            abort_if(RolesTable::cannotEdit($role), 403);

            $this->action = 'edit';
            $this->role_id = $role->id;
            $this->name = $role->name;
            $this->description = $role->description;
            $this->permission_id = $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all();
            $this->title = __('Edición del rol :name', ['name' => $role->name]);
        }
    }

    /**
     * Quien no es Administrador solo puede dar a un rol permisos que él mismo tiene; si no,
     * podría crear un rol con más acceso que el suyo. Los permisos que el rol ya tenía no cuentan.
     */
    protected function canGrantPermissions(): bool
    {
        if (auth()->user()->hasRole('Administrador')) {
            return true;
        }

        $current = $this->action === 'edit' ? Role::findOrFail($this->role_id)->permissions->pluck('id')->all() : [];

        $notOwned = Permission::whereIn('id', $this->permission_id ?? [])
            ->whereNotIn('id', $current)
            ->get()
            ->reject(fn (Permission $permission) => auth()->user()->can($permission->name));

        if ($notOwned->isEmpty()) {
            return true;
        }

        $this->addError('permission_id', __('Solo puedes asignar permisos que tú tienes: :permissions', [
            'permissions' => $notOwned->pluck('description')->implode(', '),
        ]));

        return false;
    }

    /**
     * Ids de los permisos que se pueden marcar en el formulario, o null si se pueden todos.
     *
     * @return list<int>|null
     */
    protected function grantablePermissionIds(): ?array
    {
        if (auth()->user()->hasRole('Administrador')) {
            return null;
        }

        return auth()->user()->getAllPermissions()->pluck('id')
            ->merge($this->action === 'edit' ? Role::findOrFail($this->role_id)->permissions->pluck('id') : [])
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Marca o desmarca todos los permisos de un módulo de una vez.
     */
    public function toggleGroup(string $type)
    {
        $grantable = $this->grantablePermissionIds();

        $ids = Permission::where('type', $type)->pluck('id')
            ->filter(fn ($id) => $grantable === null || in_array($id, $grantable))
            ->map(fn ($id) => (string) $id);

        $selected = collect($this->permission_id)->map(fn ($id) => (string) $id);

        $this->permission_id = $ids->diff($selected)->isEmpty()
            ? $selected->diff($ids)->values()->all()
            : $selected->merge($ids)->unique()->values()->all();
    }

    public function render()
    {
        return view('livewire.roles.crud-role', [
            'groups' => Permission::orderBy('id')->get()->groupBy('type'),
            'grantable' => $this->grantablePermissionIds(),
        ])->title($this->title);
    }

    public function store()
    {
        $this->authorizePermission('create roles');

        $data = $this->validate();

        if (! $this->canGrantPermissions()) {
            return;
        }

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'guard_name' => 'web',
            ]);
            $role->syncPermissions(Permission::whereIn('id', $this->permission_id ?? [])->get());

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('El rol ha sido registrado con éxito'),
                redirect: route('roles'),
            );

        } catch (\Throwable $e) {
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
        $this->authorizePermission('edit roles');

        // Se usa el rol que se abrió en mount(), no un id enviado desde el navegador.
        $role = Role::findOrFail($this->role_id);
        abort_if(RolesTable::cannotEdit($role), 403);

        $data = $this->validate();

        if (! $this->canGrantPermissions()) {
            return;
        }

        try {
            DB::beginTransaction();

            $role->update([
                'name' => $data['name'],
                'description' => $data['description'],
            ]);
            // Se sincroniza aunque la lista venga vacía, para poder quitarle todos los permisos.
            $role->syncPermissions(Permission::whereIn('id', $this->permission_id ?? [])->get());

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Completado!'),
                text: __('El rol ha sido actualizado con éxito'),
                redirect: route('roles'),
            );

        } catch (\Throwable $e) {
            DB::rollback();
            $this->dispatch('swalmodal',
                type: 'warning',
                title: __('¡Error!'),
                text: $this->errorMessage($e),
            );
        }
    }
}
