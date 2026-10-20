<div class="space-y-6">
    <x-common.page-band title="Categorías">
        <x-slot:subtitle>
            {{ $categories->total() }} {{ $categories->total() === 1 ? 'categoría' : 'categorías' }}
        </x-slot:subtitle>
        @can('create categories')
            <x-slot:actions>
                <a href="{{ route('categories.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Nueva categoría
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    <section aria-labelledby="listado-titulo" class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 px-5 pt-5 pb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <h2 id="listado-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Listado</h2>

            <label class="relative block sm:w-72">
                <span class="sr-only">Buscar categoría</span>
                <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar categoría…" autocomplete="off"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pr-4 pl-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
            </label>
        </div>

        @if ($categories->isEmpty())
            {{-- Estado vacío --}}
            <div class="flex flex-col items-center border-t border-gray-200 px-6 py-16 text-center dark:border-gray-800">
                <span class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    {!! \App\Helpers\MenuHelper::getIconSvg('category') !!}
                </span>
                @if ($search === '')
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Todavía no hay categorías</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Crea la primera para empezar a ordenar los productos de la carta.</p>
                @else
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Ninguna categoría coincide con «{{ $search }}»</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Prueba con otro nombre.</p>
                    <button type="button" wire:click="$set('search', '')"
                        class="mt-4 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpiar búsqueda
                    </button>
                @endif
            </div>
        @else
            <div class="custom-scrollbar overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,sortBy,gotoPage">
                <table class="w-full min-w-[560px] text-left">
                    <thead>
                        <tr class="border-y border-gray-200 bg-gray-50 text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                            <x-sortable-th field="name" :sort-field="$sortField" :sort-direction="$sortDirection" class="pl-5 sm:pl-6">Nombre</x-sortable-th>
                            <x-sortable-th field="products_count" :sort-field="$sortField" :sort-direction="$sortDirection">Productos</x-sortable-th>
                            <x-sortable-th field="created_at" :sort-field="$sortField" :sort-direction="$sortDirection">Registrada</x-sortable-th>
                            <th scope="col" class="py-3 pr-5 pl-3 text-right font-medium sm:pr-6">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($categories as $category)
                            <tr wire:key="category-{{ $category->id }}" class="transition-colors hover:bg-gray-25 dark:hover:bg-white/[0.02]">
                                <td class="py-3 pr-3 pl-5 text-sm font-medium text-gray-800 sm:pl-6 dark:text-white/90">{{ $category->name }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 tabular-nums dark:text-gray-400">
                                    @if ($category->products_count > 0)
                                        {{ $category->products_count }} {{ $category->products_count === 1 ? 'producto' : 'productos' }}
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">Sin productos</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-600 tabular-nums dark:text-gray-400">{{ $category->created_at?->format('d/m/Y') }}</td>
                                <td class="py-3 pr-5 pl-3 text-right sm:pr-6">
                                    <div class="inline-flex items-center gap-1">
                                        @can('edit categories')
                                            <button type="button" wire:click="edit({{ $category->id }})"
                                                aria-label="Editar {{ $category->name }}"
                                                class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-gray-300 dark:hover:bg-white/5">
                                                Editar
                                            </button>
                                        @endcan
                                        @can('delete categories')
                                            <button type="button" wire:click="destroyConfirm({{ $category->id }})"
                                                aria-label="Eliminar {{ $category->name }}"
                                                class="rounded-md px-2.5 py-1.5 text-sm font-medium text-error-600 transition hover:bg-error-50 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-error-500 dark:hover:bg-error-500/10">
                                                Eliminar
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($categories->lastPage() > 1)
            <div class="flex justify-end border-t border-gray-200 px-5 py-4 sm:px-6 dark:border-gray-800">
                <x-common.pager :page="$categories->currentPage()" :total="$categories->lastPage()" action="gotoPage" />
            </div>
        @endif
    </section>
</div>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
