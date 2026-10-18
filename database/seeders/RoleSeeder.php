<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web'], ['description' => 'Control completo del negocio']);
        Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web'], ['description' => 'Supervisa la operación y al equipo']);
        Role::firstOrCreate(['name' => 'Cajero', 'guard_name' => 'web'], ['description' => 'Atención y operación diaria']);
    }
}
