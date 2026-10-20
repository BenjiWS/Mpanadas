<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $admin = Role::findByName('Administrador', 'web');

        $type_model = config('constants.type_model');
        $type_description = config('constants.type_description');

        Permission::create(['name' => 'get categories', 'description' => 'Visualizar Categorias', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);
        Permission::create(['name' => 'create categories', 'description' => 'Registrar Categorias', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);
        Permission::create(['name' => 'edit categories', 'description' => 'Editar Categorias', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);
        Permission::create(['name' => 'delete categories', 'description' => 'Borrar Categorias', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);

        Permission::create(['name' => 'get products', 'description' => 'Visualizar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);
        Permission::create(['name' => 'create products', 'description' => 'Registrar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);
        Permission::create(['name' => 'edit products', 'description' => 'Editar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);
        Permission::create(['name' => 'delete products', 'description' => 'Eliminar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);

        Permission::create(['name' => 'get users', 'description' => 'Visualizar Usuarios', 'type' => $type_model['Users'], 'type_description' => $type_description['Usuarios']]);
        Permission::create(['name' => 'create users', 'description' => 'Registrar Usuarios', 'type' => $type_model['Users'], 'type_description' => $type_description['Usuarios']]);
        Permission::create(['name' => 'edit users', 'description' => 'Editar Usuarios', 'type' => $type_model['Users'], 'type_description' => $type_description['Usuarios']]);
        Permission::create(['name' => 'delete users', 'description' => 'Eliminar Usuarios', 'type' => $type_model['Users'], 'type_description' => $type_description['Usuarios']]);
        Permission::create(['name' => 'assign roles', 'description' => 'Asignar Roles', 'type' => $type_model['Users'], 'type_description' => $type_description['Usuarios']]);

        Permission::create(['name' => 'get roles', 'description' => 'Visualizar Roles', 'type' => $type_model['Roles'], 'type_description' => $type_description['Roles']]);
        Permission::create(['name' => 'create roles', 'description' => 'Registrar Roles', 'type' => $type_model['Roles'], 'type_description' => $type_description['Roles']]);
        Permission::create(['name' => 'edit roles', 'description' => 'Editar Roles', 'type' => $type_model['Roles'], 'type_description' => $type_description['Roles']]);
        Permission::create(['name' => 'delete roles', 'description' => 'Eliminar Roles', 'type' => $type_model['Roles'], 'type_description' => $type_description['Roles']]);

        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $admin->givePermissionTo($permission);
        }

        $supervisor = Role::findByName('Supervisor', 'web');

        $supervisor->givePermissionTo([
            // Categorías: todos los permisos
            'get categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Productos: todos los permisos
            'get products',
            'create products',
            'edit products',
            'delete products',

            // Usuarios: solo visualización
            'get users',
        ]);
    }
}
