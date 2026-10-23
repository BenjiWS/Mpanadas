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

        Permission::create(['name' => 'get categories', 'description' => 'Visualizar Categorías', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);
        Permission::create(['name' => 'create categories', 'description' => 'Registrar Categorías', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);
        Permission::create(['name' => 'edit categories', 'description' => 'Editar Categorías', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);
        Permission::create(['name' => 'delete categories', 'description' => 'Eliminar Categorías', 'type' => $type_model['Categories'], 'type_description' => $type_description['Categorias']]);

        Permission::create(['name' => 'get products', 'description' => 'Visualizar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);
        Permission::create(['name' => 'create products', 'description' => 'Registrar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);
        Permission::create(['name' => 'edit products', 'description' => 'Editar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);
        Permission::create(['name' => 'delete products', 'description' => 'Eliminar Productos', 'type' => $type_model['Products'], 'type_description' => $type_description['Productos']]);

        Permission::create(['name' => 'get ingredients', 'description' => 'Visualizar Ingredientes', 'type' => $type_model['Ingredients'], 'type_description' => $type_description['Ingredientes']]);
        Permission::create(['name' => 'create ingredients', 'description' => 'Registrar Ingredientes', 'type' => $type_model['Ingredients'], 'type_description' => $type_description['Ingredientes']]);
        Permission::create(['name' => 'edit ingredients', 'description' => 'Editar Ingredientes', 'type' => $type_model['Ingredients'], 'type_description' => $type_description['Ingredientes']]);
        Permission::create(['name' => 'delete ingredients', 'description' => 'Eliminar Ingredientes', 'type' => $type_model['Ingredients'], 'type_description' => $type_description['Ingredientes']]);

        Permission::create(['name' => 'get combos', 'description' => 'Visualizar Combos', 'type' => $type_model['Combos'], 'type_description' => $type_description['Combos']]);
        Permission::create(['name' => 'create combos', 'description' => 'Registrar Combos', 'type' => $type_model['Combos'], 'type_description' => $type_description['Combos']]);
        Permission::create(['name' => 'edit combos', 'description' => 'Editar Combos', 'type' => $type_model['Combos'], 'type_description' => $type_description['Combos']]);
        Permission::create(['name' => 'delete combos', 'description' => 'Eliminar Combos', 'type' => $type_model['Combos'], 'type_description' => $type_description['Combos']]);

        Permission::create(['name' => 'get offers', 'description' => 'Visualizar Ofertas', 'type' => $type_model['Offers'], 'type_description' => $type_description['Ofertas']]);
        Permission::create(['name' => 'create offers', 'description' => 'Registrar Ofertas', 'type' => $type_model['Offers'], 'type_description' => $type_description['Ofertas']]);
        Permission::create(['name' => 'edit offers', 'description' => 'Editar Ofertas', 'type' => $type_model['Offers'], 'type_description' => $type_description['Ofertas']]);
        Permission::create(['name' => 'delete offers', 'description' => 'Eliminar Ofertas', 'type' => $type_model['Offers'], 'type_description' => $type_description['Ofertas']]);

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

            // Ingredientes: todos los permisos
            'get ingredients',
            'create ingredients',
            'edit ingredients',
            'delete ingredients',

            // Combos: todos los permisos
            'get combos',
            'create combos',
            'edit combos',
            'delete combos',

            // Ofertas: todos los permisos
            'get offers',
            'create offers',
            'edit offers',
            'delete offers',

            // Usuarios: solo visualización
            'get users',
        ]);
    }
}
