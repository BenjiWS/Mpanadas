<div>
    <x-common.page-band title="Combos">
        <x-slot:actions>
            <button type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-base text-brand-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:text-brand-300">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Añadir
            </button>
            <button type="button" wire:click="descartar" @disabled(count($seleccionados) === 0)
                wire:confirm="¿Descartar {{ count($seleccionados) }} {{ count($seleccionados) === 1 ? 'combo' : 'combos' }}? Esta acción no se puede deshacer."
                class="inline-flex items-center gap-2 rounded-lg border border-gray-800 px-4 py-2 text-base text-gray-800 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:active:scale-100 dark:border-gray-400 dark:text-gray-300">
                Descartar
                @if (count($seleccionados) > 0)
                    <span class="rounded-sm bg-gray-800 px-1.5 text-xs leading-5 font-medium text-white tabular-nums dark:bg-gray-300 dark:text-gray-900">{{ count($seleccionados) }}</span>
                @endif
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="max-w-6xl">
        <label class="relative block">
            <span class="sr-only">Buscar combos</span>
            <svg class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar"
                class="h-12 w-full rounded-lg border border-gray-700/70 bg-gray-25 pr-4 pl-11 text-base text-gray-800 placeholder:text-gray-600 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-gray-500" />
        </label>

        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400" aria-live="polite">
            @if (count($seleccionados) > 0)
                {{ count($seleccionados) }} {{ count($seleccionados) === 1 ? 'seleccionado' : 'seleccionados' }} para descartar.
            @else
                Toca un combo para seleccionarlo.
            @endif
        </p>

        @if (count($this->pagina) === 0)
            <div class="mt-6 flex flex-col items-center rounded-lg border border-dashed border-gray-400 px-6 py-16 text-center dark:border-gray-700">
                @if (count($combos) === 0)
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Todavía no hay combos</h2>
                    <p class="mt-1 max-w-sm text-sm text-gray-600 dark:text-gray-400">Arma el primero con "Añadir" para ofrecer empanadas y bebidas a un precio cerrado.</p>
                @else
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Ningún combo coincide con "{{ $search }}"</h2>
                    <p class="mt-1 max-w-sm text-sm text-gray-600 dark:text-gray-400">Prueba con el nombre del combo o con un sabor.</p>
                    <button type="button" wire:click="$set('search', '')"
                        class="mt-4 rounded-lg border border-gray-800 px-4 py-2 text-sm font-medium text-gray-800 transition hover:bg-white/60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-800 dark:border-gray-400 dark:text-gray-300">
                        Limpiar búsqueda
                    </button>
                @endif
            </div>
        @else
            <ul class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8" wire:loading.class="opacity-60" wire:target="search,irAPagina">
                @foreach ($this->pagina as $combo)
                    @php $seleccionado = in_array($combo['id'], $seleccionados); @endphp
                    <li wire:key="combo-{{ $combo['id'] }}">
                        <button type="button" wire:click="toggleSeleccion({{ $combo['id'] }})" aria-pressed="{{ $seleccionado ? 'true' : 'false' }}"
                            @class([
                                'group flex h-full w-full flex-col overflow-hidden rounded-lg border bg-gray-25 text-left transition hover:-translate-y-0.5 hover:shadow-theme-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 active:translate-y-0 dark:bg-white/[0.03]',
                                'border-brand-500 ring-1 ring-brand-500' => $seleccionado,
                                'border-gray-800/70 dark:border-gray-700' => ! $seleccionado,
                            ])>
                            <span class="flex min-h-40 flex-1 flex-col p-5">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="text-lg font-medium text-gray-800 dark:text-white/90">{{ $combo['nombre'] }}</span>
                                    <span @class([
                                        'mt-0.5 flex size-5 shrink-0 items-center justify-center rounded border transition',
                                        'border-brand-600 bg-brand-600 text-white' => $seleccionado,
                                        'border-gray-400 text-transparent group-hover:border-gray-600 dark:border-gray-600' => ! $seleccionado,
                                    ]) aria-hidden="true">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                                    </span>
                                </span>
                                <span class="mt-2 space-y-0.5 text-sm text-gray-600 dark:text-gray-400">
                                    @foreach ($combo['contenido'] as $linea)
                                        <span class="block">{{ $linea }}</span>
                                    @endforeach
                                </span>
                                <span class="mt-auto pt-4 text-xs text-gray-600 tabular-nums dark:text-gray-400">
                                    Ahorra $ {{ number_format($combo['suelto'] - $combo['precio'], 2, ',', '.') }}
                                    <span class="text-gray-500 line-through">$ {{ number_format($combo['suelto'], 2, ',', '.') }}</span>
                                </span>
                            </span>

                            {{-- Franja de precio: naranja normal, durazno cuando está seleccionado --}}
                            <span @class([
                                'block border-t px-5 py-4 text-2xl font-medium tabular-nums transition-colors',
                                'border-brand-500 bg-brand-500 text-white' => ! $seleccionado,
                                'border-brand-300 bg-brand-300 text-gray-900 shadow-[inset_0_-6px_0_var(--color-brand-500)]' => $seleccionado,
                            ])>$ {{ number_format($combo['precio'], 2, ',', '.') }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-10 flex justify-end">
            <x-common.pager :page="$page" :total="$this->totalPaginas" />
        </div>
    </div>
</div>
