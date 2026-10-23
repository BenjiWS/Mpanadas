@php
    $th = 'px-4 py-4 font-bold';
@endphp

<div class="space-y-6">
    <x-common.page-band title="Ingredientes">
        <x-slot:subtitle>Lo que lleva cada empanada y lo que se puede sumar.</x-slot:subtitle>
        @can('create ingredients')
            <x-slot:actions>
                <a href="{{ route('ingredients.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-gray-25 px-5 py-3 text-base font-semibold text-brand-600 transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Añadir ingrediente
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    {{-- Indicadores --}}
    <div class="grid grid-cols-3 gap-3 pt-4 sm:gap-5">
        <x-common.stat-card label="Ingredientes" :value="$stats['ingredients']" hint="En la cocina" />
        <x-common.stat-card label="Usados como extra" :value="$stats['extras']" hint="Se pueden sumar al pedido" />
        <x-common.stat-card label="Ingredientes activos" :value="$stats['active']" hint="Disponibles para vender" />
    </div>

    {{-- Buscador y filtros --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <label class="relative block flex-1">
            <span class="sr-only">Buscar ingrediente por nombre o descripción</span>
            <svg class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar ingrediente…" autocomplete="off"
                class="h-[50px] w-full rounded-xl border border-gray-400 bg-transparent pr-4 pl-11 text-base text-gray-800 placeholder:text-gray-500 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
        </label>

        <div class="flex gap-2" role="group" aria-label="Filtrar ingredientes">
            @foreach (['todos' => 'Todos', 'activos' => 'Activos', 'inactivos' => 'Inactivos'] as $value => $label)
                <button type="button" wire:click="setFilter('{{ $value }}')" aria-pressed="{{ $filter === $value ? 'true' : 'false' }}"
                    @class([
                        'h-10 rounded-lg border px-4 text-base whitespace-nowrap transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98]',
                        'border-brand-500 font-semibold text-brand-600 dark:text-brand-300' => $filter === $value,
                        'border-gray-400 text-gray-700 hover:bg-white/50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5' => $filter !== $value,
                    ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tabla --}}
    <section aria-label="Listado de ingredientes" class="overflow-hidden rounded-xl border border-gray-400 dark:border-gray-700">
        @if ($ingredients->isEmpty())
            <div class="flex flex-col items-center px-6 py-16 text-center">
                <span class="flex size-12 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    {!! \App\Helpers\MenuHelper::getIconSvg('leaf') !!}
                </span>
                @if ($search === '' && $filter === 'todos')
                    <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Todavía no hay ingredientes</h3>
                    <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Crea los ingredientes que llevan los productos o que se pueden sumar como extra.</p>
                @else
                    <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Ningún ingrediente coincide</h3>
                    <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Prueba con otro nombre o cambia el filtro.</p>
                    <button type="button" wire:click="$set('search', ''); setFilter('todos')"
                        class="mt-4 rounded-lg border border-gray-400 px-5 py-2.5 text-base font-semibold text-gray-800 transition hover:bg-white/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-white/90 dark:hover:bg-white/5">
                        Ver todos los ingredientes
                    </button>
                @endif
            </div>
        @else
            <div class="custom-scrollbar overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,setFilter,sortBy,gotoPage">
                <table class="w-full min-w-[820px] text-left">
                    <thead>
                        <tr class="bg-brand-200 text-xs text-gray-800 dark:bg-brand-500/15 dark:text-white/90">
                            <x-sortable-th field="name" :sort-field="$sortField" :sort-direction="$sortDirection" :base="$th" class="w-[18%] pl-6">Ingrediente</x-sortable-th>
                            <th scope="col" class="{{ $th }} w-[30%]">Descripción</th>
                            <x-sortable-th field="extra_price" :sort-field="$sortField" :sort-direction="$sortDirection" :base="$th" class="w-[14%]">Precio extra</x-sortable-th>
                            <x-sortable-th field="extra_for_products_count" :sort-field="$sortField" :sort-direction="$sortDirection" :base="$th" class="w-[12%]">Extra en</x-sortable-th>
                            <x-sortable-th field="is_active" :sort-field="$sortField" :sort-direction="$sortDirection" :base="$th" class="w-[12%]">Estado</x-sortable-th>
                            <th scope="col" class="{{ $th }}">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-100 dark:divide-gray-800">
                        @foreach ($ingredients as $ingredient)
                            <tr wire:key="ingredient-{{ $ingredient->id }}" class="text-sm text-gray-800 transition-colors hover:bg-white/40 dark:text-white/90 dark:hover:bg-white/[0.02]">
                                <td @class(['py-5 pr-4 pl-6 font-bold', 'text-gray-500' => ! $ingredient->is_active])>{{ $ingredient->name }}</td>
                                <td class="max-w-0 truncate px-4 py-5" title="{{ $ingredient->description }}">
                                    @if ($ingredient->description)
                                        {{ $ingredient->description }}
                                    @else
                                        <span class="text-gray-500">Sin descripción</span>
                                    @endif
                                </td>
                                <td class="px-4 py-5 tabular-nums">$ {{ number_format($ingredient->extra_price, 2, ',', '.') }}</td>
                                <td class="px-4 py-5 tabular-nums">
                                    {{ $ingredient->extra_for_products_count }} {{ $ingredient->extra_for_products_count === 1 ? 'producto' : 'productos' }}
                                </td>
                                <td @class(['px-4 py-5', 'text-gray-500' => ! $ingredient->is_active])>{{ $ingredient->is_active ? 'Activo' : 'Inactivo' }}</td>
                                <td class="px-4 py-5">
                                    @can('edit ingredients')
                                        <button type="button" wire:click="edit({{ $ingredient->id }})" aria-label="Editar {{ $ingredient->name }}"
                                            class="inline-flex items-center gap-1 rounded-sm text-brand-600 underline-offset-4 transition hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:text-brand-300">
                                            Editar
                                            <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Pie: conteo y paginador --}}
        <div class="flex items-center justify-between gap-3 border-t border-gray-300/80 bg-cream px-6 py-3 dark:border-gray-800 dark:bg-white/[0.02]">
            <p class="text-sm text-gray-700 tabular-nums dark:text-gray-400" aria-live="polite">
                {{ $ingredients->total() }} {{ $ingredients->total() === 1 ? 'resultado' : 'resultados' }}
            </p>
            <x-common.text-pager :page="$ingredients->currentPage()" :total="$ingredients->lastPage()" />
        </div>
    </section>
</div>
