<?php

namespace App\Livewire\Concerns;

trait AuthorizesPermissions
{
    /**
     * Los botones se ocultan en la vista con @can, pero cualquier acción pública de Livewire
     * se puede llamar directamente, así que el permiso también se valida aquí.
     */
    protected function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }
}
