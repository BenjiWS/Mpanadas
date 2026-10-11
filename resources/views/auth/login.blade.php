@extends('layouts.auth')

@section('title', 'Iniciar sesión')
@section('subtitle', 'Ingresa tu email y contraseña para entrar.')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-form.input name="email" type="email" label="Email" :value="old('email')" placeholder="tu@email.com" required autofocus autocomplete="username" />

        <x-form.input name="password" type="password" label="Contraseña" placeholder="Tu contraseña" required autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-700 select-none dark:text-gray-400">
                <input type="checkbox" name="remember" class="h-5 w-5 rounded-md border-gray-300 accent-brand-500 dark:border-gray-700">
                Mantener la sesión iniciada
            </label>

            <a href="{{ route('password.request') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

        <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
            Iniciar sesión
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-700 sm:text-start dark:text-gray-400">
        ¿No tienes cuenta?
        <a href="{{ route('register') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Regístrate</a>
    </p>
@endsection
