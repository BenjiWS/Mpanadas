@php
    $submit = $action === 'store' ? 'store' : 'update';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Las categorías ordenan los productos en la carta.' : 'Actualiza los datos de esta categoría.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            @if ($action === 'edit')
                @can('delete categories')
                    <button type="button" wire:click="destroyConfirm"
                        class="rounded-lg px-4 py-2 text-base text-error-600 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-error-500 dark:text-error-500">
                        Eliminar
                    </button>
                @endcan
            @endif
            <a href="{{ route('categories') }}"
                class="rounded-lg px-4 py-2 text-base text-gray-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 dark:text-gray-300">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] disabled:opacity-60 dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear categoría' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1.8fr)_minmax(0,1fr)] lg:items-start">
        <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
            <h2 id="datos-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Datos de la categoría</h2>

            <div class="mt-6 space-y-5">
                <x-form.input name="name" label="Nombre" wire:model="name" required autofocus autocomplete="off" placeholder="Ej. Empanadas de carne" />

                <div>
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                    <input id="description" type="text" wire:model="description" maxlength="255" placeholder="Opcional · ej. Sabores clásicos y especiales"
                        @class([
                            'shadow-theme-xs h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                            'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('description'),
                            'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('description'),
                        ]) />
                    @error('description')
                        <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section aria-labelledby="estado-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
            <div class="flex items-center justify-between gap-4">
                <h2 id="estado-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Activa</h2>
                <button type="button" role="switch" wire:click="$toggle('is_active')" aria-labelledby="estado-titulo" aria-describedby="estado-ayuda"
                    aria-checked="{{ $is_active ? 'true' : 'false' }}"
                    @class([
                        'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
                        'bg-brand-500' => $is_active,
                        'bg-gray-300 dark:bg-gray-700' => ! $is_active,
                    ])>
                    <span @class([
                        'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
                        'translate-x-[22px]' => $is_active,
                        'translate-x-0.5' => ! $is_active,
                    ])></span>
                </button>
            </div>
            <p id="estado-ayuda" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ $is_active ? 'Se muestra en la carta.' : 'Inactiva: oculta en la carta hasta reactivarla.' }}
            </p>
        </section>
    </div>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
