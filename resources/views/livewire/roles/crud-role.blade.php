@php
    $submit = $action === 'store' ? 'store' : 'update';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-6">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Ponle nombre al rol y marca lo que puede hacer.' : 'Actualiza el nombre y los permisos de este rol.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('roles') }}"
                class="btn btn-ghost">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}"
                class="btn btn-primary">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear rol' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    {{-- Datos del rol --}}
    <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 id="datos-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Datos del rol</h2>

        <div class="mt-5 grid gap-5 md:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]">
            <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" placeholder="Ej. Cajero" />
            <x-form.input name="description" label="Descripción" wire:model="description" autocomplete="off" placeholder="Ej. Atención y operación diaria" />
        </div>
    </section>

    {{-- Permisos --}}
    <section aria-labelledby="permisos-titulo" aria-describedby="permisos-ayuda">
        <div class="mb-4">
            <h2 id="permisos-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Permisos</h2>
            <p id="permisos-ayuda" class="mt-1 text-sm text-gray-500 dark:text-gray-400">Marca lo que este rol puede hacer en cada módulo.</p>
            @error('permission_id')
                <p class="mt-2 text-theme-xs text-error-500" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Columnas tipo mosaico: los grupos tienen distinta altura y así no quedan huecos --}}
        <div class="gap-5 sm:columns-2 xl:columns-3">
            @foreach ($groups as $type => $permissions)
                @php
                    $selected = collect($permission_id)->map(fn ($id) => (int) $id);
                    $granted = $permissions->whereIn('id', $selected)->count();
                    $allSelected = $granted === $permissions->count();
                @endphp

                <div wire:key="group-{{ $type }}" role="group" aria-labelledby="group-{{ $type }}-title" class="mb-5 break-inside-avoid rounded-xl border border-gray-300/80 bg-white/70 p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 id="group-{{ $type }}-title" class="text-base font-semibold text-gray-800 dark:text-white/90">{{ config("constants.type_labels.$type", $permissions->first()->type_description) }}</h3>
                            <p class="mt-0.5 text-theme-xs text-gray-500 tabular-nums dark:text-gray-400">{{ $granted }} de {{ $permissions->count() }} permisos</p>
                        </div>
                        <button type="button" wire:click="toggleGroup('{{ $type }}')"
                            class="shrink-0 rounded-md px-2.5 py-1.5 text-sm font-medium text-brand-700 transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-brand-300 dark:hover:bg-brand-500/10">
                            {{ $allSelected ? 'Quitar todos' : 'Marcar todos' }}
                        </button>
                    </div>

                    <div class="mt-4 space-y-1 border-t border-gray-100 pt-3 dark:border-gray-800">
                        @foreach ($permissions as $permission)
                            @php
                                $disabled = $grantable !== null && ! in_array($permission->id, $grantable);
                            @endphp
                            <label wire:key="permission-{{ $permission->id }}" @class([
                                'flex items-center gap-3 rounded-md px-2 py-2 text-sm select-none',
                                'cursor-pointer text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]' => ! $disabled,
                                'cursor-not-allowed text-gray-400 dark:text-gray-600' => $disabled,
                            ])>
                                <input type="checkbox" wire:model.live="permission_id" value="{{ $permission->id }}" @disabled($disabled)
                                    class="size-4 rounded border-gray-300 accent-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700" />
                                {{ $permission->description }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
