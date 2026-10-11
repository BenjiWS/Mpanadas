@extends('layouts.auth')

@section('title', 'Recuperar contraseña')
@section('subtitle', 'Te enviaremos un enlace por email para crear una contraseña nueva.')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-form.input name="email" type="email" label="Email" :value="old('email')" placeholder="tu@email.com" required autofocus autocomplete="username" />

        <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
            Enviar enlace
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-gray-700 sm:text-start dark:text-gray-400">
        <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Volver a iniciar sesión</a>
    </p>
@endsection
