<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Un usuario por rol para entrar al dashboard en local.
     * Todos usan la contraseña "password".
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'lastname' => 'M Empanadas', 'username' => 'admin', 'email' => 'admin@mempanadas.test', 'role' => 'Administrador'],
            ['name' => 'Sofía', 'lastname' => 'Martínez', 'username' => 'supervisor', 'email' => 'supervisor@mempanadas.test', 'role' => 'Supervisor'],
            ['name' => 'Diego', 'lastname' => 'Fernández', 'username' => 'cajero', 'email' => 'cajero@mempanadas.test', 'role' => 'Cajero'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'lastname' => $data['lastname'],
                    'username' => $data['username'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles($data['role']);
        }
    }
}
