<div class="space-y-8">
    <x-common.page-band title="Mi perfil">
        <x-slot:subtitle>Tu información y la seguridad de tu cuenta en un solo lugar.</x-slot:subtitle>
        <x-slot:actions>
            <button type="submit" form="profile-form" wire:loading.attr="disabled" wire:target="updateProfile"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] disabled:opacity-60 dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                <span wire:loading.remove wire:target="updateProfile">Guardar cambios</span>
                <span wire:loading wire:target="updateProfile">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1.8fr)_minmax(0,1fr)] lg:items-start">
        {{-- Información personal --}}
        <section aria-labelledby="info-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
            <h2 id="info-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Información personal</h2>

            <span class="mt-6 flex size-20 items-center justify-center rounded-full bg-brand-200 text-2xl font-bold text-gray-800 dark:bg-brand-500/20 dark:text-brand-200" aria-hidden="true">
                {{ auth()->user()->initials() }}
            </span>

            <form id="profile-form" wire:submit="updateProfile" class="mt-6 space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="given-name" />
                    <x-form.input name="lastname" label="Apellido" wire:model="lastname" required autocomplete="family-name" />
                </div>
                <x-form.input name="email" type="email" label="Correo electrónico" wire:model="email" required autocomplete="email" />
            </form>

            <p class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                Usuario: <span class="font-mono text-gray-800 dark:text-white/90">{{ auth()->user()->username }}</span>
                · Rol: <span class="text-gray-800 dark:text-white/90">{{ auth()->user()->getRoleNames()->first() ?? 'Sin rol' }}</span>
            </p>
        </section>

        {{-- Seguridad de la cuenta --}}
        <section aria-labelledby="seguridad-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
            <h2 id="seguridad-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Seguridad de la cuenta</h2>

            @if ($changingPassword)
                <form wire:submit="updatePassword" class="mt-6 space-y-5">
                    <x-form.input name="current_password" type="password" label="Contraseña actual" wire:model="current_password" required autocomplete="current-password" />
                    <x-form.input name="password" type="password" label="Contraseña nueva" wire:model="password" required autocomplete="new-password" />
                    <x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" wire:model="password_confirmation" required autocomplete="new-password" />

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword"
                            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] disabled:opacity-60">
                            Guardar contraseña
                        </button>
                        <button type="button" wire:click="togglePasswordForm"
                            class="rounded-lg px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-white/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:text-gray-300 dark:hover:bg-white/5">
                            Cancelar
                        </button>
                    </div>
                </form>
            @else
                <div class="mt-6">
                    <p class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">Contraseña</p>
                    <div class="flex h-11 items-center rounded-lg border border-gray-300 px-4 text-lg tracking-[0.2em] text-gray-700 select-none dark:border-gray-700 dark:text-gray-300">
                        <span aria-hidden="true">••••••••••••</span>
                        <span class="sr-only">Oculta por seguridad</span>
                    </div>
                    <button type="button" wire:click="togglePasswordForm"
                        class="mt-5 rounded-lg border border-gray-400/70 bg-white/40 px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98] dark:border-gray-700 dark:bg-transparent dark:text-white/90 dark:hover:bg-white/5">
                        Cambiar contraseña
                    </button>
                </div>
            @endif

            <div class="mt-8 border-t border-gray-300 pt-6 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Sesión actual</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Panel de {{ config('app.name') }}</p>

                <form method="POST" action="{{ route('logout') }}" class="mt-5">
                    @csrf
                    <button type="submit"
                        class="rounded-lg border border-gray-400/70 bg-white/40 px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98] dark:border-gray-700 dark:bg-transparent dark:text-white/90 dark:hover:bg-white/5">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
