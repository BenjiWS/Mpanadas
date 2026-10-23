<div class="space-y-8">
    <x-common.page-band title="Productos">
        <x-slot:subtitle>El sabor de tu negocio, bien organizado.</x-slot:subtitle>
        @can('create products')
            <x-slot:actions>
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Añadir producto
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    {{-- Buscador y filtros --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <label class="relative block flex-1">
            <span class="sr-only">Buscar productos por nombre o categoría</span>
            <svg class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar productos…" autocomplete="off"
                class="h-12 w-full rounded-xl border border-gray-400/70 bg-transparent pr-4 pl-11 text-base text-gray-800 placeholder:text-gray-500 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
        </label>

        <div class="flex gap-2" role="group" aria-label="Filtrar productos">
            @foreach (['todos' => 'Todos', 'activos' => 'Activos', 'inactivos' => 'Inactivos'] as $value => $label)
                <button type="button" wire:click="setFilter('{{ $value }}')" aria-pressed="{{ $filter === $value ? 'true' : 'false' }}"
                    @class([
                        'h-11 rounded-lg border px-5 text-base whitespace-nowrap transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98]',
                        'border-brand-500 font-semibold text-brand-600 dark:text-brand-300' => $filter === $value,
                        'border-gray-400/70 text-gray-700 hover:bg-white/50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5' => $filter !== $value,
                    ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($products->isEmpty())
        {{-- Estado vacío --}}
        <div class="flex flex-col items-center rounded-xl border border-gray-400/70 px-6 py-16 text-center dark:border-gray-800">
            <span class="flex size-12 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                {!! \App\Helpers\MenuHelper::getIconSvg('empanada') !!}
            </span>
            @if ($search === '' && $filter === 'todos')
                <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Todavía no hay productos</h3>
                <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Agrega el primero para que aparezca en la carta.</p>
            @else
                <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Ningún producto coincide</h3>
                <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Prueba con otro nombre o cambia el filtro.</p>
                <button type="button" wire:click="$set('search', ''); setFilter('todos')"
                    class="mt-4 rounded-lg border border-gray-400/70 px-5 py-2.5 text-base font-semibold text-gray-800 transition hover:bg-white/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-white/90 dark:hover:bg-white/5">
                    Ver todos los productos
                </button>
            @endif
        </div>
    @else
        {{-- Columnas según el ancho mínimo de la tarjeta (máx. 23rem cada una); los productos por página = columnas × 2 filas, así la página cabe en pantalla y no quedan huecos --}}
        <div class="grid gap-6 sm:grid-cols-[repeat(auto-fill,minmax(18rem,1fr))]" wire:loading.class="opacity-60" wire:target="search,setFilter,gotoPage,toggleAvailability,perPage"
            x-data="{
                timer: null,
                sync() {
                    const columns = getComputedStyle(this.$el).gridTemplateColumns.split(' ').filter(Boolean).length || 1;
                    // Con una sola columna (celular) se baja con scroll: 6 por página en vez de 2.
                    const perPage = columns === 1 ? 6 : columns * 2;
                    if (perPage !== Number(this.$wire.perPage)) this.$wire.set('perPage', perPage);
                },
            }"
            x-init="sync(); new ResizeObserver(() => { clearTimeout(timer); timer = setTimeout(() => sync(), 200); }).observe($el)">
            @foreach ($products as $product)
                <article wire:key="product-{{ $product->id }}" aria-labelledby="product-{{ $product->id }}-name"
                    class="group flex w-full flex-col sm:max-w-[23rem] overflow-hidden rounded-xl border border-gray-400/70 dark:border-gray-700">
                    <div class="p-5">
                        <div class="aspect-[16/9] overflow-hidden rounded-lg bg-brand-50 dark:bg-brand-500/10">
                            @if ($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="" loading="lazy"
                                    @class(['size-full object-cover', 'opacity-60 grayscale' => ! $product->available]) />
                            @else
                                <div class="flex size-full items-center justify-center text-brand-400 [&>svg]:size-10 dark:text-brand-300" aria-hidden="true">
                                    {!! \App\Helpers\MenuHelper::getIconSvg('empanada') !!}
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 flex items-start justify-between gap-3">
                            <h2 id="product-{{ $product->id }}-name" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">{{ $product->name }}</h2>

                            @can('edit products')
                                <button type="button" wire:click="toggleAvailability({{ $product->id }})"
                                    aria-label="{{ $product->available ? 'Marcar como inactivo' : 'Marcar como activo' }}: {{ $product->name }}"
                                    title="{{ $product->available ? 'Marcar como inactivo' : 'Marcar como activo' }}"
                                    @class([
                                        'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-95',
                                        'bg-brand-100 text-brand-600 hover:bg-brand-200 dark:bg-brand-500/15 dark:text-brand-300' => $product->available,
                                        'bg-gray-200 text-gray-600 hover:bg-gray-300 dark:bg-white/10 dark:text-gray-400' => ! $product->available,
                                    ])>
                                    {{ $product->available ? 'Activo' : 'Inactivo' }}
                                </button>
                            @else
                                <span @class([
                                    'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    'bg-brand-100 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300' => $product->available,
                                    'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-400' => ! $product->available,
                                ])>{{ $product->available ? 'Activo' : 'Inactivo' }}</span>
                            @endcan
                        </div>

                        <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-400">
                            {{ $product->categories->pluck('name')->implode(' · ') ?: 'Sin categoría' }}
                        </p>
                    </div>

                    {{-- Precio y acciones --}}
                    <div class="mt-auto flex items-center justify-between gap-3 border-b-4 border-brand-500 bg-brand-200/70 px-5 py-3.5 transition-colors group-hover:bg-brand-500 dark:bg-brand-500/15 dark:group-hover:bg-brand-500/40">
                        <p class="text-xl font-bold text-gray-800 tabular-nums transition-colors group-hover:text-white dark:text-white/90">
                            <span class="sr-only">Precio:</span> $ {{ number_format($product->price, 2, ',', '.') }}
                        </p>

                        @canany(['edit products', 'delete products'])
                            <div class="flex items-center gap-2">
                                @can('delete products')
                                    <button type="button" wire:click="destroyConfirm({{ $product->id }})"
                                        aria-label="Eliminar {{ $product->name }}" title="Eliminar"
                                        class="flex size-9 items-center justify-center rounded-lg text-gray-700 transition hover:bg-white/60 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white group-hover:text-white group-hover:hover:text-error-600 dark:text-gray-300">
                                        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M5.5 7l1 12a2 2 0 0 0 2 1.8h7a2 2 0 0 0 2-1.8l1-12M9 7V4.8A.8.8 0 0 1 9.8 4h4.4a.8.8 0 0 1 .8.8V7"/></svg>
                                    </button>
                                @endcan
                                @can('edit products')
                                    <button type="button" wire:click="edit({{ $product->id }})"
                                        aria-label="Editar {{ $product->name }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-gray-25 px-3 py-1.5 text-sm font-semibold text-gray-800 transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                        Editar
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                                    </button>
                                @endcan
                            </div>
                        @endcanany
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pie fijo abajo: el conteo y el paginador quedan a la vista al bajar --}}
        <div class="sticky bottom-0 z-10 -mx-4 -mb-4 flex flex-col gap-3 border-t border-gray-300/80 bg-gray-50/95 px-4 py-3 backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between md:-mx-6 md:-mb-6 md:px-6 dark:border-gray-800 dark:bg-gray-900/95">
            <p class="text-sm text-gray-600 tabular-nums dark:text-gray-400" aria-live="polite">
                {{ $products->count() }} de {{ $products->total() }} {{ $products->total() === 1 ? 'producto' : 'productos' }}
            </p>
            <x-common.pager :page="$products->currentPage()" :total="$products->lastPage()" action="gotoPage" />
        </div>
    @endif
</div>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
