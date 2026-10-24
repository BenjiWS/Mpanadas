@php($submit = $action === 'store' ? 'store' : 'update')

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Crea el acceso de una persona al sistema.' : 'Actualiza los datos y el acceso de este usuario.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('users') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}" class="btn btn-primary">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear usuario' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <x-common.form-layout>
        {{-- Datos y acceso, en un solo panel --}}
        <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 id="datos-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Datos del usuario</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Con el username o el correo y la contraseña entra al sistema.</p>

            <div class="mt-7 space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" />
                    <x-form.input name="lastname" label="Apellido" wire:model="lastname" required autocomplete="off" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input name="username" label="Username" wire:model="username" required autocomplete="off" />
                    <x-form.input name="email" type="email" label="Correo" wire:model="email" required autocomplete="off" />
                </div>

                <div>
                    <x-form.input name="password" type="password" label="Contraseña" wire:model="password" :required="$action === 'store'" autocomplete="new-password" />
                    <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ $action === 'store' ? 'Mínimo 8 caracteres.' : 'Déjala en blanco para no cambiarla.' }}
                    </p>
                </div>
            </div>

            {{-- Acceso --}}
            <div class="mt-7 space-y-6 border-t border-gray-200 pt-6 dark:border-gray-800">
                @can('assign roles')
                    <div>
                        <label for="role_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Rol<span class="text-error-500">*</span>
                        </label>
                        <select id="role_id" wire:model.live="role_id" @disabled($isSelf)
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

                <x-form.switch-row id="estado" title="Usuario activo" model="is_active" :checked="$is_active" :disabled="$isSelf"
                    :help="$isSelf ? 'No puedes desactivar tu propio usuario.' : 'Un usuario inactivo se conserva, pero no puede entrar.'" />
            </div>
        </section>

        {{-- Vista previa: cómo lo ve el equipo --}}
        <x-slot:preview>
            <aside aria-labelledby="preview-titulo" class="space-y-4">
                <x-common.preview-heading>Así lo ve el equipo</x-common.preview-heading>
                <x-common.user-card :roles="$roles->pluck('name', 'id')" />
            </aside>
        </x-slot:preview>

        {{-- Contexto: lo que puede hacer con el rol elegido y, al editar, sus fechas --}}
        <x-slot:context>
            <aside aria-label="Permisos del usuario" class="space-y-4">
                <x-common.role-abilities :abilities="$abilities" :empty="$role_id ? 'Este rol todavía no tiene permisos.' : 'Elige un rol para ver lo que podrá hacer.'" />

                @if ($action === 'edit')
                    <x-common.facts :items="[['Creado', $createdAt], ['Última edición', $updatedAt]]" />
                @endif
            </aside>
        </x-slot:context>
    </x-common.form-layout>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
