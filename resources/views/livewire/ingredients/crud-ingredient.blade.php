@php
    $submit = $action === 'store' ? 'store' : 'update';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Los ingredientes se usan como extras en los productos.' : 'Actualiza los datos de este ingrediente.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('ingredients') }}"
                class="rounded-lg px-4 py-2 text-base text-gray-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 dark:text-gray-300">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] disabled:opacity-60 dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear ingrediente' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1.8fr)_minmax(0,1fr)] lg:items-start">
        <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
            <h2 id="datos-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Datos del ingrediente</h2>

            <div class="mt-6 space-y-5">
                <div class="grid gap-5 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" placeholder="Ej. Queso" />

                    <div>
                        <label for="extra_price" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Precio como extra<span class="text-error-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400" aria-hidden="true">$</span>
                            <input id="extra_price" type="number" step="0.01" min="0" inputmode="decimal" wire:model="extra_price" placeholder="0,00" aria-describedby="extra_price-help"
                                @class([
                                    'shadow-theme-xs h-11 w-full rounded-lg border bg-transparent pr-4 pl-8 text-sm text-gray-800 tabular-nums placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                                    'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('extra_price'),
                                    'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('extra_price'),
                                ]) />
                        </div>
                        @error('extra_price')
                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p id="extra_price-help" class="-mt-2 text-theme-xs text-gray-500 dark:text-gray-400">
                    Es el precio general. En cada producto puedes poner uno distinto.
                </p>

                <div>
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                    <textarea id="description" wire:model="description" rows="3" placeholder="Opcional"
                        @class([
                            'shadow-theme-xs w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                            'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('description'),
                            'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('description'),
                        ])></textarea>
                    @error('description')
                        <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section aria-labelledby="estado-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
            <div class="flex items-center justify-between gap-4">
                <h2 id="estado-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Activo</h2>
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
                {{ $is_active ? 'Se puede ofrecer como extra.' : 'Desactivado: no se ofrece como extra en ningún producto.' }}
            </p>
        </section>
    </div>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
