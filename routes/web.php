<?php

use App\Livewire\Categories\CategoriesTable;
use App\Livewire\Categories\CrudCategory;
use App\Livewire\Combos;
use App\Livewire\Contador;
use App\Livewire\Dashboard;
use App\Livewire\Ingredients\CrudIngredient;
use App\Livewire\Ingredients\IngredientsTable;
use App\Livewire\Products\CrudProduct;
use App\Livewire\Products\ProductsTable;
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
    Route::livewire('/combos', Combos::class)->name('combos');

    Route::livewire('/products', ProductsTable::class)->name('products')->middleware('permission:get products');
    Route::livewire('/products/create', CrudProduct::class)->name('products.create')->middleware('permission:create products');
    Route::livewire('/products/{product}/edit', CrudProduct::class)->name('products.edit')->middleware('permission:edit products');

    Route::livewire('/ingredients', IngredientsTable::class)->name('ingredients')->middleware('permission:get ingredients');
    Route::livewire('/ingredients/create', CrudIngredient::class)->name('ingredients.create')->middleware('permission:create ingredients');
    Route::livewire('/ingredients/{ingredient}/edit', CrudIngredient::class)->name('ingredients.edit')->middleware('permission:edit ingredients');

    Route::livewire('/categories', CategoriesTable::class)->name('categories')->middleware('permission:get categories');
    Route::livewire('/categories/create', CrudCategory::class)->name('categories.create')->middleware('permission:create categories');
    Route::livewire('/categories/{category}/edit', CrudCategory::class)->name('categories.edit')->middleware('permission:edit categories');

    Route::livewire('/users', UsersTable::class)->name('users')->middleware('permission:get users');
    Route::livewire('/users/create', CrudUser::class)->name('users.create')->middleware('permission:create users');
    Route::livewire('/users/{user}/edit', CrudUser::class)->name('users.edit')->middleware('permission:edit users');

    Route::livewire('/roles', RolesTable::class)->name('roles')->middleware('permission:get roles');
    Route::livewire('/roles/create', CrudRole::class)->name('roles.create')->middleware('permission:create roles');
    Route::livewire('/roles/{role}/edit', CrudRole::class)->name('roles.edit')->middleware('permission:edit roles');
});
