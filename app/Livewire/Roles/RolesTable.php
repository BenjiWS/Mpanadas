<?php

namespace App\Livewire\Roles;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Title('Roles')]
class RolesTable extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithPagination;

    public $search = '';

    /** todos | en_uso (con usuarios asignados) | sin_permisos */
    public $filter = 'todos';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setFilter(string $filter)
    {
        $this->filter = in_array($filter, ['todos', 'en_uso', 'sin_permisos'], true) ? $filter : 'todos';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.roles.roles-table', [
            'roles' => Role::with('permissions')
                ->withCount('users')
                ->where('name', 'LIKE', "%{$this->search}%")
                ->when($this->filter === 'en_uso', fn ($query) => $query->has('users'))
                ->when($this->filter === 'sin_permisos', fn ($query) => $query->doesntHave('permissions'))
                ->orderBy('name')
                ->paginate(9),
            // Nombre visible de cada módulo, para listar en la tarjeta los módulos a los que el rol tiene acceso.
            'groups' => Permission::all()->groupBy('type')
                ->map(fn ($permissions, $type) => config("constants.type_labels.$type", $permissions->first()->type_description)),
        ]);
    }

    /**
     * Motivo por el que el usuario actual no puede editar el rol, o null si sí puede.
     * También lo usa CrudRole, porque a la edición se puede entrar directo por la URL.
     */
    public static function cannotEdit(Role $role): ?string
    {
        if (auth()->user()->hasRole($role->name)) {
            return __('No puedes modificar un rol que tú mismo tienes asignado.');
        }

        if ($role->name === 'Administrador') {
            return __('No puedes modificar el rol Administrador.');
        }

        return null;
    }

    protected function cannotDelete(Role $role): ?string
    {
        if (auth()->user()->hasRole($role->name)) {
            return __('No puedes eliminar un rol que tú mismo tienes asignado.');
        }

        if ($role->name === 'Administrador') {
            return __('No puedes eliminar el rol Administrador.');
        }

        if ($role->users()->count() > 0) {
            return __('No puedes eliminar un rol que tiene usuarios asignados.');
        }

        return null;
    }

    protected function notAllowed(string $reason): void
    {
        $this->dispatch('swalmodal',
            type: 'warning',
            title: __('¡Acción no permitida!'),
            text: $reason,
        );
    }

    public function edit($id)
    {
        $this->authorizePermission('edit roles');

        $role = Role::findOrFail($id);

        if ($reason = static::cannotEdit($role)) {
            $this->notAllowed($reason);

            return;
        }

        $this->redirectRoute('roles.edit', $role);
    }

    public function destroyConfirm($id)
    {
        $this->authorizePermission('delete roles');

        if ($reason = $this->cannotDelete(Role::findOrFail($id))) {
            $this->notAllowed($reason);

            return;
        }

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar este rol?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $id,
        );
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $this->authorizePermission('delete roles');

        // destroy se puede llamar sin pasar por destroyConfirm, así que se revisa otra vez.
        $role = Role::findOrFail($id);

        if ($reason = $this->cannotDelete($role)) {
            $this->notAllowed($reason);

            return;
        }

        try {
            DB::beginTransaction();

            $role->delete();

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('El rol fue eliminado con éxito'),
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
