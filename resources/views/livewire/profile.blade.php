<div>
    <x-common.page-breadcrumb pageTitle="Mi perfil" />

    <div class="space-y-6">
        <!-- Datos personales -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6 flex items-center gap-4">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-500 text-xl font-semibold text-white">
                    {{ auth()->user()->initials() }}
                </span>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Datos personales</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Actualiza tu nombre y email.</p>
                </div>
            </div>

            <form wire:submit="updateProfile" class="space-y-5 lg:max-w-xl">
                <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="name" />
                <x-form.input name="email" type="email" label="Email" wire:model="email" required autocomplete="username" />

                <div class="flex items-center gap-4">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:opacity-50" wire:loading.attr="disabled" wire:target="updateProfile">
                        Guardar cambios
                    </button>

                    @if (session('profile-status'))
                        <span class="text-sm text-success-600 dark:text-success-500">{{ session('profile-status') }}</span>
                    @endif
                </div>
            </form>
        </div>

        <!-- Contraseña -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Cambiar contraseña</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Usa una contraseña larga y difícil de adivinar.</p>
            </div>

            <form wire:submit="updatePassword" class="space-y-5 lg:max-w-xl">
                <x-form.input name="current_password" type="password" label="Contraseña actual" wire:model="current_password" required autocomplete="current-password" />
                <x-form.input name="password" type="password" label="Contraseña nueva" wire:model="password" required autocomplete="new-password" />
                <x-form.input name="password_confirmation" type="password" label="Confirmar contraseña" wire:model="password_confirmation" required autocomplete="new-password" />

                <div class="flex items-center gap-4">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:opacity-50" wire:loading.attr="disabled" wire:target="updatePassword">
                        Cambiar contraseña
                    </button>

                    @if (session('password-status'))
                        <span class="text-sm text-success-600 dark:text-success-500">{{ session('password-status') }}</span>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
