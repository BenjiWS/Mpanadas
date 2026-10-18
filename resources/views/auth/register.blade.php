@extends('layouts.auth')

@section('title', 'Crear cuenta')
@section('subtitle', 'Completa tus datos para registrarte.')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input name="name" label="Nombre" :value="old('name')" placeholder="Tu nombre" required autofocus autocomplete="given-name" />

            <x-form.input name="lastname" label="Apellido" :value="old('lastname')" placeholder="Tu apellido" required autocomplete="family-name" />
        </div>

        <x-form.input name="username" label="Usuario" :value="old('username')" placeholder="Letras, números, punto, - o _" required autocomplete="off" />

        <x-form.input name="email" type="email" label="Email" :value="old('email')" placeholder="tu@email.com" required autocomplete="username" />

        <x-form.input name="password" type="password" label="Contraseña" placeholder="Mínimo 8 caracteres" required autocomplete="new-password" />

        <x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" placeholder="Repite la contraseña" required autocomplete="new-password" />

        <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
            Crear cuenta
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-700 sm:text-start dark:text-gray-400">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Inicia sesión</a>
    </p>
@endsection
