<?php

use App\Livewire\Combos;
use App\Livewire\Contador;
use App\Livewire\Dashboard;
use App\Livewire\Productos;
use App\Livewire\Profile;
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
});
