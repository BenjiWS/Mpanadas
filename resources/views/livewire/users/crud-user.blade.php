<form wire:submit="{{ $action }}" class="space-y-6">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Crea el acceso de una persona al sistema.' : 'Actualiza los datos y el acceso de este usuario.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('users') }}"
                class="rounded-lg px-4 py-2 text-base text-gray-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 dark:text-gray-300">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $action }}"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/60 px-4 py-2 text-base font-medium text-brand-700 transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] disabled:opacity-60 dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                <span wire:loading.remove wire:target="{{ $action }}">{{ $action === 'store' ? 'Crear usuario' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $action }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:items-start">
        {{-- Datos del usuario --}}
        <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 id="datos-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Datos del usuario</h2>

            <div class="mt-5 space-y-5">
                <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" />
                <x-form.input name="email" type="email" label="Correo" wire:model="email" required autocomplete="off" />

                <div>
                    <x-form.input name="password" type="password" label="Contraseña" wire:model="password" :required="$action === 'store'" autocomplete="new-password" />
                    <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ $action === 'store' ? 'Mínimo 8 caracteres.' : 'Déjala en blanco para no cambiarla.' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Acceso --}}
        <section aria-labelledby="acceso-titulo" class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 id="acceso-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Acceso</h2>

            <div class="mt-5 space-y-5">
                @can('assign roles')
                    <div>
                        <label for="role_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Rol<span class="text-error-500">*</span>
                        </label>
                        <select id="role_id" wire:model="role_id" @disabled($isSelf)
                            @class([
                                'shadow-theme-xs h-11 w-full rounded-lg border bg-transparent px-3.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-white/[0.03]',
                                'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('role_id'),
                                'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('role_id'),
                            ])>
                            <option value="">Seleccione un rol</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @if ($isSelf)
                            <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">No puedes cambiar tu propio rol.</p>
                        @endif
                        @error('role_id')
                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endcan

                <div>
                    <div class="flex items-center justify-between gap-4">
                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-400">Usuario activo</label>
                        <button type="button" id="is_active" role="switch" wire:click="$toggle('is_active')" @disabled($isSelf)
                            aria-checked="{{ $is_active ? 'true' : 'false' }}"
                            @class([
                                'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:cursor-not-allowed disabled:opacity-50',
                                'bg-brand-500' => $is_active,
                                'bg-gray-200 dark:bg-gray-700' => ! $is_active,
                            ])>
                            <span @class([
                                'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
                                'translate-x-[22px]' => $is_active,
                                'translate-x-0.5' => ! $is_active,
                            ])></span>
                        </button>
                    </div>
                    <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ $isSelf ? 'No puedes desactivar tu propio usuario.' : 'Un usuario inactivo se conserva, pero no debería poder entrar.' }}
                    </p>
                </div>
            </div>
        </section>
    </div>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
