@php
    $statusLabels = ['vigente' => 'Vigente', 'programada' => 'Programada', 'finalizada' => 'Finalizada', 'pausada' => 'Pausada'];
@endphp

<div class="space-y-6">
    <x-common.page-band title="Ofertas">
        <x-slot:subtitle>Promociones que invitan a volver.</x-slot:subtitle>
        @can('create offers')
            <x-slot:actions>
                <a href="{{ route('offers.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-gray-25 px-5 py-3 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Añadir oferta
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    {{-- Buscador y filtros --}}
    <div class="flex flex-col gap-3 pt-2 lg:flex-row lg:items-center">
        <label class="relative block flex-1">
            <span class="sr-only">Buscar ofertas por nombre, producto, categoría o combo</span>
            <svg class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar una oferta…" autocomplete="off"
                class="h-[50px] w-full rounded-xl border border-gray-400 bg-transparent pr-4 pl-11 text-base text-gray-800 placeholder:text-gray-500 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
        </label>

        <div class="flex gap-2" role="group" aria-label="Filtrar ofertas">
            @foreach (['todas' => 'Todas', 'vigentes' => 'Vigentes', 'finalizadas' => 'Finalizadas'] as $value => $label)
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

    @if ($offers->isEmpty())
        {{-- Estado vacío --}}
        <div class="flex flex-col items-center rounded-xl border border-gray-400/70 px-6 py-16 text-center dark:border-gray-800">
            <span class="flex size-12 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                {!! \App\Helpers\MenuHelper::getIconSvg('tag') !!}
            </span>
            @if ($search === '' && $filter === 'todas')
                <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Todavía no hay ofertas</h3>
                <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Crea la primera para dar un descuento en productos, categorías o combos.</p>
            @else
                <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Ninguna oferta coincide</h3>
                <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Prueba con otro nombre o cambia el filtro.</p>
                <button type="button" wire:click="$set('search', ''); setFilter('todas')"
                    class="mt-4 rounded-lg border border-gray-400/70 px-5 py-2.5 text-base font-semibold text-gray-800 transition hover:bg-white/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-white/90 dark:hover:bg-white/5">
                    Ver todas las ofertas
                </button>
            @endif
        </div>
    @else
        {{-- Ajusta las ofertas por página a columnas × 2 filas: la página cabe en pantalla y no quedan huecos --}}
        <div class="grid gap-5 sm:grid-cols-[repeat(auto-fill,minmax(20rem,23rem))]" wire:loading.class="opacity-60" wire:target="search,setFilter,gotoPage,perPage"
            x-data="{
                timer: null,
                sync() {
                    const columns = getComputedStyle(this.$el).gridTemplateColumns.split(' ').filter(Boolean).length || 1;
                    const perPage = columns * 2;
                    if (perPage !== Number(this.$wire.perPage)) this.$wire.set('perPage', perPage);
                },
            }"
            x-init="sync(); new ResizeObserver(() => { clearTimeout(timer); timer = setTimeout(() => sync(), 200); }).observe($el)">
            @foreach ($offers as $offer)
                <article wire:key="offer-{{ $offer->id }}" aria-labelledby="offer-{{ $offer->id }}-name"
                    class="flex flex-col rounded-xl border border-gray-400 bg-cream px-7 pt-8 pb-7 dark:border-gray-700 dark:bg-white/[0.03]">
                    <p class="px-2 text-sm font-semibold text-brand-600 dark:text-brand-300">{{ $statusLabels[$offer->status()] }}</p>

                    <p class="mt-9 text-[4rem] leading-none font-light text-brand-600 tabular-nums dark:text-brand-300">
                        <span class="sr-only">Descuento:</span> {{ $offer->headline() }}
                    </p>

                    <h2 id="offer-{{ $offer->id }}-name" class="mt-7 text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90">{{ $offer->name }}</h2>
                    <p class="mt-1 line-clamp-2 text-[15px] text-gray-600 dark:text-gray-400">
                        {{ $offer->description ?: ($offer->targetsLabel() ?: 'Sin productos asignados') }}
                    </p>

                    <div class="mt-auto pt-4">
                        {{-- Con días u horario se muestra el horario; si no, las fechas de la vigencia --}}
                        <p class="border-t border-gray-400 pt-4 text-sm text-gray-700 tabular-nums dark:border-gray-700 dark:text-gray-300">
                            {{ $offer->weekdays || $offer->start_time ? $offer->scheduleLabel() : $offer->periodLabel() }}
                        </p>

                        @can('edit offers')
                            <button type="button" wire:click="edit({{ $offer->id }})"
                                class="mt-6 inline-flex h-[38px] w-full items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-gray-50 text-[15px] font-semibold text-gray-800 transition hover:border-brand-400 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                Editar oferta
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                            </button>
                        @endcan
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pie fijo abajo, solo cuando hay más de una página --}}
        @if ($offers->hasPages())
            <div class="sticky bottom-0 z-10 -mx-4 -mb-4 flex flex-col gap-3 border-t border-gray-300/80 bg-gray-50/95 px-4 py-3 backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between md:-mx-6 md:-mb-6 md:px-6 dark:border-gray-800 dark:bg-gray-900/95">
                <p class="text-sm text-gray-600 tabular-nums dark:text-gray-400" aria-live="polite">
                    {{ $offers->count() }} de {{ $offers->total() }} ofertas
                </p>
                <x-common.pager :page="$offers->currentPage()" :total="$offers->lastPage()" action="gotoPage" />
            </div>
        @endif
    @endif
</div>
