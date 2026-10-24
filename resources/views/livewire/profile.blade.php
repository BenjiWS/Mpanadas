<div class="space-y-8">
    <x-common.page-band title="Mi perfil">
        <x-slot:subtitle>Tu información y la seguridad de tu cuenta en un solo lugar.</x-slot:subtitle>
        <x-slot:actions>
            <button type="submit" form="profile-form" wire:loading.attr="disabled" wire:target="updateProfile" class="btn btn-primary">
                <span wire:loading.remove wire:target="updateProfile">Guardar cambios</span>
                <span wire:loading wire:target="updateProfile">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <x-common.form-layout>
        {{-- Información personal --}}
        <section aria-labelledby="info-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 id="info-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Información personal</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Así te identifica el equipo en el sistema.</p>

            <form id="profile-form" wire:submit="updateProfile" class="mt-7 space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="given-name" />
                    <x-form.input name="lastname" label="Apellido" wire:model="lastname" required autocomplete="family-name" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="username" label="Username" wire:model="username" required autocomplete="username" />
                    <x-form.input name="email" type="email" label="Correo electrónico" wire:model="email" required autocomplete="email" />
                </div>

                {{-- Solo lectura: el rol lo asigna un administrador desde Usuarios --}}
                <div>
                    <label for="role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Rol</label>
                    <input id="role" type="text" value="{{ $roleName ?? 'Sin rol' }}" readonly aria-describedby="role-help"
                        class="h-11 w-full cursor-default rounded-lg border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600 focus:outline-hidden dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400" />
                    <p id="role-help" class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Solo un administrador puede cambiar tu rol.</p>
                </div>
            </form>
        </section>

        {{-- Vista previa: cómo te ve el equipo --}}
        <x-slot:preview>
            <aside aria-labelledby="preview-titulo" class="space-y-4">
                <x-common.preview-heading>Así te ve el equipo</x-common.preview-heading>
                <x-common.user-card :role="$roleName" :status="false" />
            </aside>
        </x-slot:preview>

        {{-- Contexto: seguridad de la cuenta y lo que puedes hacer --}}
        <x-slot:context>
            <aside aria-label="Tu cuenta" class="space-y-4">
                <section aria-labelledby="seguridad-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 px-6 py-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 id="seguridad-titulo" class="text-sm font-semibold text-gray-800 dark:text-white/90">Seguridad de la cuenta</h2>

                    @if ($changingPassword)
                        <form wire:submit="updatePassword" class="mt-4 space-y-4">
                            <x-form.input name="current_password" type="password" label="Contraseña actual" wire:model="current_password" required autocomplete="current-password" />
                            <x-form.input name="password" type="password" label="Contraseña nueva" wire:model="password" required autocomplete="new-password" />
                            <x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" wire:model="password_confirmation" required autocomplete="new-password" />

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="btn btn-sm btn-primary">
                                    Guardar contraseña
                                </button>
                                <button type="button" wire:click="togglePasswordForm" class="btn btn-sm btn-ghost">Cancelar</button>
                            </div>
                        </form>
                    @else
                        <div class="mt-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Contraseña</p>
                                <p class="mt-0.5 text-lg tracking-[0.2em] text-gray-700 select-none dark:text-gray-300">
                                    <span aria-hidden="true">••••••••</span>
                                    <span class="sr-only">Oculta por seguridad</span>
                                </p>
                            </div>
                            <button type="button" wire:click="togglePasswordForm" class="btn btn-sm btn-outline shrink-0">Cambiar</button>
                        </div>
                    @endif

                    <div class="mt-5 flex items-center justify-between gap-4 border-t border-gray-200 pt-5 dark:border-gray-800">
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sesión actual</p>
                            <p class="mt-0.5 truncate text-sm font-medium text-gray-800 dark:text-white/90">Panel de {{ config('app.name') }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ghost">Cerrar sesión</button>
                        </form>
                    </div>
                </section>

                <x-common.role-abilities :abilities="$abilities" title="Lo que puedes hacer" empty="Tu rol todavía no tiene permisos." />

                <x-common.facts :items="[['En el equipo desde', $createdAt]]" />
            </aside>
        </x-slot:context>
    </x-common.form-layout>
</div>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
