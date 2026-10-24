<?php

namespace App\Livewire\Concerns;

use Spatie\Permission\Models\Role;

trait DescribesRoleAbilities
{
    /**
     * Permisos de un rol en lenguaje simple, agrupados por módulo y en el orden del seeder:
     * [['label' => 'Productos', 'abilities' => 'ver, crear, editar y eliminar'], ...].
     */
    protected function roleAbilities(?Role $role): array
    {
        if (! $role) {
            return [];
        }

        $verbs = ['get' => 'ver', 'create' => 'crear', 'edit' => 'editar', 'delete' => 'eliminar', 'assign' => 'asignar roles'];

        return $role->permissions->sortBy('id')->groupBy('type')
            ->map(function ($permissions, $type) use ($verbs) {
                $abilities = $permissions->map(fn ($permission) => $verbs[strtok($permission->name, ' ')] ?? $permission->description)->values();

                return [
                    'label' => config("constants.type_labels.$type", $permissions->first()->type_description),
                    'abilities' => $abilities->count() > 1
                        ? $abilities->slice(0, -1)->implode(', ').' y '.$abilities->last()
                        : $abilities->first(),
                ];
            })
            ->values()
            ->all();
    }
}
