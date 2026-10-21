<div class="space-y-6">
    <x-common.page-band title="Ingredientes">
        <x-slot:subtitle>
            {{ $ingredients->total() }} {{ $ingredients->total() === 1 ? 'ingrediente' : 'ingredientes' }}
        </x-slot:subtitle>
        @can('create ingredients')
            <x-slot:actions>
                <a href="{{ route('ingredients.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Nuevo ingrediente
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    <section aria-labelledby="listado-titulo" class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 px-5 pt-5 pb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <h2 id="listado-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Listado</h2>

            <label class="relative block sm:w-72">
                <span class="sr-only">Buscar ingrediente</span>
                <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar ingrediente…" autocomplete="off"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pr-4 pl-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
            </label>
        </div>

        @if ($ingredients->isEmpty())
            {{-- Estado vacío --}}
            <div class="flex flex-col items-center border-t border-gray-200 px-6 py-16 text-center dark:border-gray-800">
                <span class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    {!! \App\Helpers\MenuHelper::getIconSvg('leaf') !!}
                </span>
                @if ($search === '')
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Todavía no hay ingredientes</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Crea los ingredientes que quieras ofrecer como extras.</p>
                @else
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Ningún ingrediente coincide con «{{ $search }}»</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Prueba con otro nombre.</p>
                    <button type="button" wire:click="$set('search', '')"
                        class="mt-4 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpiar búsqueda
                    </button>
                @endif
            </div>
        @else
            <div class="custom-scrollbar overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,sortBy,gotoPage,toggleActive">
                <table class="w-full min-w-[680px] text-left">
                    <thead>
                        <tr class="border-y border-gray-200 bg-gray-50 text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                            <x-sortable-th field="name" :sort-field="$sortField" :sort-direction="$sortDirection" class="pl-5 sm:pl-6">Nombre</x-sortable-th>
                            <x-sortable-th field="extra_price" :sort-field="$sortField" :sort-direction="$sortDirection">Precio como extra</x-sortable-th>
                            <x-sortable-th field="extra_for_products_count" :sort-field="$sortField" :sort-direction="$sortDirection">Extra en</x-sortable-th>
                            <th scope="col" class="px-3 py-3 text-center font-medium">Activo</th>
                            <th scope="col" class="py-3 pr-5 pl-3 text-right font-medium sm:pr-6">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($ingredients as $ingredient)
                            <tr wire:key="ingredient-{{ $ingredient->id }}" class="transition-colors hover:bg-gray-25 dark:hover:bg-white/[0.02]">
                                <td class="py-3 pr-3 pl-5 sm:pl-6">
                                    <p @class([
                                        'text-sm font-medium',
                                        'text-gray-800 dark:text-white/90' => $ingredient->is_active,
                                        'text-gray-400 dark:text-gray-500' => ! $ingredient->is_active,
                                    ])>{{ $ingredient->name }}</p>
                                    @if ($ingredient->description)
                                        <p class="mt-0.5 max-w-xs truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ $ingredient->description }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm font-medium text-gray-800 tabular-nums dark:text-white/90">$ {{ number_format($ingredient->extra_price, 2, ',', '.') }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 tabular-nums dark:text-gray-400">
                                    @if ($ingredient->extra_for_products_count > 0)
                                        {{ $ingredient->extra_for_products_count }} {{ $ingredient->extra_for_products_count === 1 ? 'producto' : 'productos' }}
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">Ningún producto</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @can('edit ingredients')
                                        <button type="button" role="switch" wire:click="toggleActive({{ $ingredient->id }})"
                                            aria-checked="{{ $ingredient->is_active ? 'true' : 'false' }}"
                                            aria-label="Activo: {{ $ingredient->name }}"
                                            @class([
                                                'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
                                                'bg-brand-500' => $ingredient->is_active,
                                                'bg-gray-200 dark:bg-gray-700' => ! $ingredient->is_active,
                                            ])>
                                            <span @class([
                                                'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
                                                'translate-x-[22px]' => $ingredient->is_active,
                                                'translate-x-0.5' => ! $ingredient->is_active,
                                            ])></span>
                                        </button>
                                    @else
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $ingredient->is_active ? 'Sí' : 'No' }}</span>
                                    @endcan
                                </td>
                                <td class="py-3 pr-5 pl-3 text-right sm:pr-6">
                                    <div class="inline-flex items-center gap-1">
                                        @can('edit ingredients')
                                            <button type="button" wire:click="edit({{ $ingredient->id }})"
                                                aria-label="Editar {{ $ingredient->name }}"
                                                class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-gray-300 dark:hover:bg-white/5">
                                                Editar
                                            </button>
                                        @endcan
                                        @can('delete ingredients')
                                            <button type="button" wire:click="destroyConfirm({{ $ingredient->id }})"
                                                aria-label="Eliminar {{ $ingredient->name }}"
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

        @if ($ingredients->lastPage() > 1)
            <div class="flex justify-end border-t border-gray-200 px-5 py-4 sm:px-6 dark:border-gray-800">
                <x-common.pager :page="$ingredients->currentPage()" :total="$ingredients->lastPage()" action="gotoPage" />
            </div>
        @endif
    </section>
</div>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
