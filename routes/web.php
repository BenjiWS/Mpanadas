<?php

use App\Livewire\Contador;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/dashboard', Dashboard::class)->name('dashboard');
Route::livewire('/contador', Contador::class);
