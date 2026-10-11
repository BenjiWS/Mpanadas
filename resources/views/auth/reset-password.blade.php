@extends('layouts.auth')

@section('title', 'Nueva contraseña')
@section('subtitle', 'Elige una contraseña nueva para tu cuenta.')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form.input name="email" type="email" label="Email" :value="old('email', $request->email)" required autocomplete="username" />

        <x-form.input name="password" type="password" label="Contraseña nueva" placeholder="Mínimo 8 caracteres" required autofocus autocomplete="new-password" />

        <x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" placeholder="Repite la contraseña" required autocomplete="new-password" />

        <button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
            Guardar contraseña
        </button>
    </form>
@endsection
