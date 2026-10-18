<?php

use App\Livewire\Combos;
use App\Livewire\Contador;
use App\Livewire\Dashboard;
use App\Livewire\Productos;
use App\Livewire\Profile;
use App\Livewire\Roles\CrudRole;
use App\Livewire\Roles\RolesTable;
use App\Livewire\Users\CrudUser;
use App\Livewire\Users\UsersTable;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Login, registro, logout y recuperar contraseña: las rutas las registra Fortify

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('/contador', Contador::class);
    Route::livewire('/profile', Profile::class)->name('profile');
    Route::livewire('/productos', Productos::class)->name('productos');
    Route::livewire('/combos', Combos::class)->name('combos');

    Route::livewire('/users', UsersTable::class)->name('users')->middleware('permission:get users');
    Route::livewire('/users/create', CrudUser::class)->name('users.create')->middleware('permission:create users');
    Route::livewire('/users/{user}/edit', CrudUser::class)->name('users.edit')->middleware('permission:edit users');

    Route::livewire('/roles', RolesTable::class)->name('roles')->middleware('permission:get roles');
    Route::livewire('/roles/create', CrudRole::class)->name('roles.create')->middleware('permission:create roles');
    Route::livewire('/roles/{role}/edit', CrudRole::class)->name('roles.edit')->middleware('permission:edit roles');
});
