@php
    $submit = $action === 'store' ? 'store' : 'update';
    $shown = $products->take(8);
    $activeOffers = $offers->filter(fn ($offer) => $offer->status() === 'vigente');
    $statusLabels = ['vigente' => 'Vigente', 'programada' => 'Programada', 'pausada' => 'Pausada'];
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Las categorías ordenan los productos en la carta.' : 'Actualiza los datos de esta categoría.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('categories') }}" class="btn btn-ghost">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}" class="btn btn-primary">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear categoría' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <x-common.form-layout>
        {{-- Datos --}}
        <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 id="datos-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Datos de la categoría</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">El nombre y la descripción son los que se leen en la carta.</p>

            <div class="mt-7 space-y-6">
                <x-form.input name="name" label="Nombre" wire:model="name" required autofocus autocomplete="off" placeholder="Ej. Empanadas de carne" />

                <div x-data>
                    <div class="mb-1.5 flex items-baseline justify-between gap-3">
                        <label for="description" class="text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                        <span class="text-theme-xs text-gray-500 tabular-nums dark:text-gray-400" x-text="($wire.description || '').length + ' / 255'" aria-hidden="true"></span>
                    </div>
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

                {{-- Estado, dentro del mismo panel --}}
                <x-form.switch-row id="estado" title="Visible en la carta" model="is_active" :checked="$is_active"
                    :help="$is_active ? 'Sus productos se muestran bajo esta categoría.' : 'Oculta: no aparece en la carta hasta reactivarla.'"
                    class="border-t border-gray-200 pt-6 dark:border-gray-800" />
            </div>
        </section>

        {{-- Vista previa en la carta --}}
        <x-slot:preview>
        <aside aria-labelledby="preview-titulo" class="space-y-4" x-data>
            <x-common.preview-heading>Así se ve en la carta</x-common.preview-heading>

            <article class="overflow-hidden rounded-xl border border-gray-300/80 bg-gray-25 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-7 pt-8 pb-7 transition" :class="{ 'opacity-50 grayscale': ! $wire.is_active }">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold tracking-wide text-brand-600 dark:text-brand-300">Sección de la carta</p>
                        <span x-show="! $wire.is_active" x-cloak class="rounded-md bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-300">Oculta en la carta</span>
                    </div>

                    <h3 class="mt-3 text-3xl font-bold tracking-tight text-balance break-words text-gray-800 dark:text-white/90"
                        x-text="$wire.name || 'Nombre de la categoría'" :class="{ 'text-gray-400 dark:text-gray-500': ! $wire.name }">{{ $name ?: 'Nombre de la categoría' }}</h3>
                    <p class="mt-2 text-base text-pretty break-words text-gray-600 dark:text-gray-400"
                        x-text="$wire.description || 'Una frase corta que la describa.'" :class="{ 'italic text-gray-400 dark:text-gray-500': ! $wire.description }">{{ $description ?: 'Una frase corta que la describa.' }}</p>

                    {{-- Productos con línea de puntos hasta el precio, como en una carta impresa --}}
                    <div class="mt-7 border-t border-gray-300/80 pt-5 dark:border-gray-800">
                        @if ($action === 'store')
                            <ul class="space-y-3.5" aria-hidden="true">
                                @foreach ([['w-36', '2,50'], ['w-28', '2,75'], ['w-40', '3,10']] as [$width, $price])
                                    <li class="flex items-center gap-3">
                                        <span class="h-2.5 {{ $width }} rounded-full bg-gray-200 dark:bg-white/10"></span>
                                        <span class="flex-1 border-b-2 border-dotted border-gray-200 dark:border-white/10"></span>
                                        <span class="text-sm text-gray-300 tabular-nums dark:text-gray-600">$ {{ $price }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="mt-5 text-sm text-gray-500 dark:text-gray-400">Los productos que le asignes aparecerán aquí.</p>
                        @elseif ($products->isEmpty())
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Todavía no tiene productos. Se asignan desde el formulario de cada producto.
                            </p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($shown as $product)
                                    <li wire:key="preview-product-{{ $product->id }}" class="flex items-baseline gap-3 text-sm">
                                        @can('edit products')
                                            <a href="{{ route('products.edit', $product) }}"
                                                @class(['min-w-0 truncate font-medium underline-offset-4 transition hover:text-brand-600 hover:underline focus-visible:outline-2 focus-visible:outline-brand-500', 'text-gray-800 dark:text-white/90' => $product->available, 'text-gray-400 line-through dark:text-gray-500' => ! $product->available])>{{ $product->name }}</a>
                                        @else
                                            <span @class(['min-w-0 truncate font-medium', 'text-gray-800 dark:text-white/90' => $product->available, 'text-gray-400 line-through dark:text-gray-500' => ! $product->available])>{{ $product->name }}</span>
                                        @endcan
                                        <span class="min-w-6 flex-1 translate-y-[-3px] border-b-2 border-dotted border-gray-300 dark:border-gray-700" aria-hidden="true"></span>
                                        @if ($product->available)
                                            <span class="font-semibold text-gray-800 tabular-nums dark:text-white/90">$ {{ number_format($product->price, 2, ',', '.') }}</span>
                                        @else
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Agotado</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            @if ($products->count() > $shown->count())
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">y {{ $products->count() - $shown->count() }} más</p>
                            @endif
                        @endif
                    </div>
                </div>

                @if ($action === 'edit')
                    <div class="flex items-center justify-between gap-3 border-t border-gray-300/80 bg-cream/60 px-7 py-3 text-sm text-gray-700 tabular-nums dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                        <span>{{ $products->count() }} {{ $products->count() === 1 ? 'producto' : 'productos' }}</span>
                        <span>{{ $activeOffers->count() }} {{ $activeOffers->count() === 1 ? 'oferta vigente' : 'ofertas vigentes' }}</span>
                    </div>
                @endif
            </article>
        </aside>
        </x-slot:preview>

        {{-- Contexto (solo al editar) --}}
        <x-slot:context>
        @if ($action === 'edit')
            <aside aria-label="Contexto de la categoría" class="space-y-4">
                {{-- Ofertas que incluyen la categoría --}}
                <section aria-labelledby="ofertas-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 px-6 py-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h2 id="ofertas-titulo" class="text-sm font-semibold text-gray-800 dark:text-white/90">Ofertas que la incluyen</h2>
                    @if ($offers->isEmpty())
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Ninguna oferta activa o programada la incluye.</p>
                        @can('create offers')
                            <a href="{{ route('offers.create') }}" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-brand-600 underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-brand-300">
                                Crear una oferta
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                            </a>
                        @endcan
                    @else
                        <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach ($offers as $offer)
                                @php $status = $offer->status(); @endphp
                                <li wire:key="preview-offer-{{ $offer->id }}" class="flex items-center gap-4 py-3">
                                    <span class="w-16 shrink-0 text-xl font-light text-brand-600 tabular-nums dark:text-brand-300">{{ $offer->headline() }}</span>
                                    <div class="min-w-0 flex-1">
                                        @can('edit offers')
                                            <a href="{{ route('offers.edit', $offer) }}" class="block truncate text-sm font-medium text-gray-800 underline-offset-4 hover:text-brand-600 hover:underline focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-white/90">{{ $offer->name }}</a>
                                        @else
                                            <span class="block truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $offer->name }}</span>
                                        @endcan
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $statusLabels[$status] ?? '' }} · {{ $offer->periodLabel() }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Datos de uso --}}
                <x-common.facts :items="[
                    ['Productos', $products->count()],
                    ['Agotados', $products->where('available', false)->count()],
                    ['Creada', $createdAt],
                    ['Última edición', $updatedAt],
                ]" />
            </aside>
        @endif
        </x-slot:context>

        {{-- Eliminar: explica antes de tocar el botón por qué no se puede --}}
        <x-slot:danger>
        @if ($action === 'edit')
            @can('delete categories')
                <x-common.danger-zone title="Eliminar categoría"
                    :text="$products->isEmpty()
                        ? 'No tiene productos, así que se puede eliminar. Si solo quieres ocultarla, desactívala.'
                        : 'Tiene ' . $products->count() . ' ' . ($products->count() === 1 ? 'producto' : 'productos') . '. Muévelos a otra categoría antes de eliminarla, o desactívala para ocultarla de la carta.'"
                    button="Eliminar categoría" :disabled="$products->isNotEmpty()" />
            @endcan
        @endif
        </x-slot:danger>
    </x-common.form-layout>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
