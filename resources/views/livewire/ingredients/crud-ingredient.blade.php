@php
    $submit = $action === 'store' ? 'store' : 'update';
    $linkClass = 'min-w-0 truncate font-medium text-gray-800 underline-offset-4 transition hover:text-brand-600 hover:underline focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-white/90';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Los ingredientes se usan como extras en los productos.' : 'Actualiza los datos de este ingrediente.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('ingredients') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}" class="btn btn-primary">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear ingrediente' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <x-common.form-layout>
        {{-- Datos --}}
        <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 id="datos-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Datos del ingrediente</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">El nombre y el precio son los que se ven en caja al sumarlo a un pedido.</p>

            <div class="mt-7 space-y-6">
                <div>
                    <div class="grid gap-5 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                        <x-form.input name="name" label="Nombre" wire:model="name" required autofocus autocomplete="off" placeholder="Ej. Queso" />

                        <div>
                            <label for="extra_price" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Precio como extra<span class="text-error-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400" aria-hidden="true">$</span>
                                <input id="extra_price" type="number" step="0.01" min="0" inputmode="decimal" wire:model="extra_price" placeholder="0,00" aria-describedby="extra_price-help"
                                    @class([
                                        'shadow-theme-xs h-11 w-full rounded-lg border bg-transparent pr-4 pl-8 text-sm font-semibold text-gray-800 tabular-nums placeholder:font-normal placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                                        'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('extra_price'),
                                        'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('extra_price'),
                                    ]) />
                            </div>
                            @error('extra_price')
                                <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p id="extra_price-help" class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">Es el precio general. En cada producto puedes poner uno distinto.</p>
                </div>

                <div x-data>
                    <div class="mb-1.5 flex items-baseline justify-between gap-3">
                        <label for="description" class="text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                        <span class="text-theme-xs text-gray-500 tabular-nums dark:text-gray-400" x-text="($wire.description || '').length + ' / 1000'" aria-hidden="true"></span>
                    </div>
                    <textarea id="description" wire:model="description" rows="3" maxlength="1000" placeholder="Opcional · ej. Mozzarella rallada"
                        @class([
                            'shadow-theme-xs w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                            'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('description'),
                            'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('description'),
                        ])></textarea>
                    @error('description')
                        <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Estado, dentro del mismo panel --}}
                <x-form.switch-row id="estado" title="Se ofrece como extra" model="is_active" :checked="$is_active"
                    :help="$is_active ? 'Aparece en caja para sumarlo a los productos que lo permiten.' : 'Desactivado: no se ofrece como extra en ningún producto.'"
                    class="border-t border-gray-200 pt-6 dark:border-gray-800" />
            </div>
        </section>

        {{-- Vista previa: cómo se ofrece en caja --}}
        <x-slot:preview>
            <aside aria-labelledby="preview-titulo" class="space-y-4"
                x-data="{
                    money(value) {
                        const number = Number(value);
                        return Number.isFinite(number) && value !== '' && value !== null
                            ? number.toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                            : '0,00';
                    },
                }">
                <x-common.preview-heading>Así se ofrece en caja</x-common.preview-heading>

                <article class="overflow-hidden rounded-xl border border-gray-300/80 bg-gray-25 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="px-7 pt-8 pb-7 transition" :class="{ 'opacity-50 grayscale': ! $wire.is_active }">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold tracking-wide text-brand-600 dark:text-brand-300">Extra para sumar al pedido</p>
                            <span x-show="! $wire.is_active" x-cloak class="rounded-md bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-300">No se ofrece</span>
                        </div>

                        <h3 class="mt-3 text-3xl font-bold tracking-tight text-balance break-words text-gray-800 dark:text-white/90"
                            x-text="$wire.name || 'Nombre del ingrediente'" :class="{ 'text-gray-400 dark:text-gray-500': ! $wire.name }">{{ $name ?: 'Nombre del ingrediente' }}</h3>
                        <p class="mt-2 line-clamp-3 text-base text-pretty break-words text-gray-600 dark:text-gray-400"
                            x-text="$wire.description || 'Una descripción corta, opcional.'" :class="{ 'italic text-gray-400 dark:text-gray-500': ! $wire.description }">{{ $description ?: 'Una descripción corta, opcional.' }}</p>

                        <div class="mt-7 border-t border-gray-300/80 pt-5 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Precio como extra</p>
                            <p class="mt-1 text-4xl font-light tracking-tight text-brand-600 tabular-nums dark:text-brand-300">
                                + $ <span x-text="money($wire.extra_price)">{{ number_format((float) $extra_price, 2, ',', '.') }}</span>
                            </p>

                            {{-- Botón tal como aparece en caja al armar un pedido --}}
                            <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">En caja se ve así</p>
                            <div class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-brand-300 bg-brand-50 px-4 py-2.5 text-sm font-semibold text-brand-700 dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-300" aria-hidden="true">
                                <span class="min-w-0 truncate">+ <span x-text="$wire.name || 'Ingrediente'">{{ $name ?: 'Ingrediente' }}</span></span>
                                <span class="shrink-0 tabular-nums">$ <span x-text="money($wire.extra_price)">{{ number_format((float) $extra_price, 2, ',', '.') }}</span></span>
                            </div>
                        </div>
                    </div>

                    @if ($action === 'edit')
                        <div class="flex items-center justify-between gap-3 border-t border-gray-300/80 bg-cream/60 px-7 py-3 text-sm text-gray-700 tabular-nums dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                            <span>Por defecto en {{ $defaultIn->count() }}</span>
                            <span>Extra en {{ $extraIn->count() }} {{ $extraIn->count() === 1 ? 'producto' : 'productos' }}</span>
                        </div>
                    @endif
                </article>
            </aside>
        </x-slot:preview>

        {{-- Contexto (solo al editar): dónde se usa --}}
        <x-slot:context>
            @if ($action === 'edit')
                <aside aria-label="Dónde se usa el ingrediente" class="space-y-4">
                    @foreach ([
                        ['id' => 'por-defecto', 'title' => 'Lo llevan por defecto', 'items' => $defaultIn, 'empty' => 'Ningún producto lo lleva por defecto.'],
                        ['id' => 'como-extra', 'title' => 'Se ofrece como extra en', 'items' => $extraIn, 'empty' => 'Ningún producto lo ofrece como extra.'],
                    ] as $group)
                        <section aria-labelledby="{{ $group['id'] }}-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 px-6 py-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <h2 id="{{ $group['id'] }}-titulo" class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $group['title'] }}</h2>
                            @if ($group['items']->isEmpty())
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $group['empty'] }}</p>
                            @else
                                <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                                    @foreach ($group['items'] as $product)
                                        <li wire:key="{{ $group['id'] }}-{{ $product->id }}" class="flex items-center justify-between gap-3 py-2.5">
                                            @can('edit products')
                                                <a href="{{ route('products.edit', $product) }}" class="{{ $linkClass }}">{{ $product->name }}</a>
                                            @else
                                                <span class="min-w-0 truncate font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</span>
                                            @endcan

                                            @if ($group['id'] === 'por-defecto')
                                                <span class="shrink-0 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                                    {{ $product->pivot->is_removable ? 'Quitable' : 'Obligatorio' }}
                                                </span>
                                            @else
                                                <span class="shrink-0 text-gray-700 tabular-nums dark:text-gray-300">
                                                    + $ {{ number_format((float) ($product->pivot->price ?? $extra_price), 2, ',', '.') }}
                                                    @if (is_null($product->pivot->price))
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">general</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach

                    <x-common.facts :items="[
                        ['Por defecto', $defaultIn->count()],
                        ['Como extra', $extraIn->count()],
                        ['En ventas', $salesCount],
                        ['Última edición', $updatedAt],
                    ]" />
                </aside>
            @endif
        </x-slot:context>

        {{-- Eliminar: explica antes de tocar el botón por qué no se puede --}}
        <x-slot:danger>
            @if ($action === 'edit')
                @can('delete ingredients')
                    <x-common.danger-zone title="Eliminar ingrediente"
                        :text="$deleteBlocker ?? 'No se usa en productos ni en ventas, así que se puede eliminar. Si solo quieres dejar de ofrecerlo, desactívalo.'"
                        button="Eliminar ingrediente" :disabled="$deleteBlocker !== null" />
                @endcan
            @endif
        </x-slot:danger>
    </x-common.form-layout>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
