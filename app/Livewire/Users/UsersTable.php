<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\AuthorizesPermissions;
use App\Livewire\Concerns\ReportsErrors;
use App\Livewire\Concerns\WithSorting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Usuarios')]
class UsersTable extends Component
{
    use AuthorizesPermissions, ReportsErrors, WithPagination, WithSorting;

    public $search = '';

    protected function sortableColumns(): array
    {
        return ['name', 'email', 'created_at'];
    }

    protected function defaultSort(): array
    {
        return ['name', 'asc'];
    }

    /**
     * Motivo por el que no se pueden modificar los datos del usuario, o null si sí se puede.
     * Lo usa también CrudUser al abrir la página de edición.
     */
    public static function cannotEdit(User $user): ?string
    {
        if ($user->hasRole('Administrador') && ! auth()->user()->hasRole('Administrador')) {
            return __('No puedes modificar datos de un Administrador.');
        }

        return null;
    }

    protected function cannotDelete(User $user): ?string
    {
        if ($user->id === auth()->id()) {
            return __('No puedes eliminar tu propio usuario mientras estás logueado.');
        }

        if ($user->hasRole('Administrador')) {
            return __('No puedes eliminar a un Administrador.');
        }

        // sales.user_id no permite borrar al usuario; se desactiva en su lugar.
        if ($user->sales()->exists()) {
            return __('Este usuario tiene ventas registradas. Desactívalo en lugar de eliminarlo.');
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

    public function render()
    {
        return view('livewire.users.users-table', [
            'users' => $this->applySorting(User::with('roles')->where(function ($query) {
                $query->where('name', 'LIKE', "%{$this->search}%")
                    ->orWhere('email', 'LIKE', "%{$this->search}%")
                    ->orWhereHas('roles', function ($q) {
                        $q->where('name', 'LIKE', "%{$this->search}%");
                    });
            }))
                ->paginate(10),
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function edit(User $user)
    {
        $this->authorizePermission('edit users');

        if ($reason = self::cannotEdit($user)) {
            $this->notAllowed($reason);

            return;
        }

        $this->redirectRoute('users.edit', $user);
    }

    public function destroyConfirm($id)
    {
        $this->authorizePermission('delete users');

        if ($reason = $this->cannotDelete(User::findOrFail($id))) {
            $this->notAllowed($reason);

            return;
        }

        $this->dispatch('swalConfirm',
            type: 'error',
            title: __('¿Está seguro de eliminar este usuario?'),
            text: __('¡No podrás revertir tu decisión!'),
            id: $id,
        );
    }

    #[On('destroy')]
    public function destroy($id)
    {
        $this->authorizePermission('delete users');

        // destroy se puede llamar sin pasar por destroyConfirm, así que se revisa otra vez.
        $user = User::findOrFail($id);

        if ($reason = $this->cannotDelete($user)) {
            $this->notAllowed($reason);

            return;
        }

        try {
            DB::beginTransaction();

            $user->syncRoles([]);
            $user->delete();

            DB::commit();

            $this->dispatch('swalmodal',
                type: 'success',
                title: __('¡Eliminado!'),
                text: __('El usuario fue eliminado con éxito'),
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
