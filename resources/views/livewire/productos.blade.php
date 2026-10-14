<div class="space-y-6">
    <x-common.page-band title="Productos">
        <x-slot:subtitle>
            {{ $this->resumen['total'] }} productos · {{ $this->resumen['activos'] }} activos · {{ $this->resumen['destacados'] }} destacados
        </x-slot:subtitle>
        <x-slot:actions>
            <button type="button"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-base text-brand-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:text-brand-300">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo producto
            </button>
        </x-slot:actions>
    </x-common.page-band>

    {{-- Filtros --}}
    <section aria-label="Filtros" class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto] lg:grid-cols-[minmax(0,1fr)_12rem_12rem_auto] lg:items-center">
            <label class="relative block md:col-span-3 lg:col-span-1">
                <span class="sr-only">Buscar por nombre</span>
                <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre…"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pr-4 pl-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
            </label>

            <label class="block">
                <span class="sr-only">Categoría</span>
                <select wire:model.live="categoria"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-700 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">Categoría: todas</option>
                    @foreach ($this->categorias as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="sr-only">Estado</span>
                <select wire:model.live="estado"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-sm text-gray-700 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">Estado: todos</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
            </label>

            <label class="flex h-11 cursor-pointer items-center gap-2.5 px-1 text-sm text-gray-700 select-none dark:text-gray-300">
                <input type="checkbox" wire:model.live="soloDestacados"
                    class="size-4 rounded border-gray-300 accent-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700" />
                Solo destacados
            </label>
        </div>
    </section>

    {{-- Listado --}}
    <section aria-labelledby="listado-titulo" class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-baseline justify-between gap-4 px-5 pt-5 pb-4 sm:px-6">
            <h2 id="listado-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Listado</h2>
            <p class="text-theme-xs text-gray-500 tabular-nums dark:text-gray-400" aria-live="polite">
                {{ count($this->filtrados) }} {{ count($this->filtrados) === 1 ? 'resultado' : 'resultados' }}
            </p>
        </div>

        @if (count($this->pagina) === 0)
            {{-- Estado vacío --}}
            <div class="flex flex-col items-center border-t border-gray-200 px-6 py-16 text-center dark:border-gray-800">
                <span class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    {!! \App\Helpers\MenuHelper::getIconSvg('empanada') !!}
                </span>
                @if (count($this->productos) === 0)
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Todavía no hay productos</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Crea el primero para que aparezca en la carta y en la página de inicio.</p>
                @else
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Ningún producto coincide con los filtros</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Prueba con otro nombre o quita algún filtro.</p>
                    <button type="button" wire:click="limpiarFiltros"
                        class="mt-4 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpiar filtros
                    </button>
                @endif
            </div>
        @else
            <div class="custom-scrollbar overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,categoria,estado,soloDestacados,irAPagina">
                <table class="w-full min-w-[860px] text-left">
                    <thead>
                        <tr class="border-y border-gray-200 bg-gray-50 text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                            <th scope="col" class="w-16 py-3 pr-3 pl-5 font-medium sm:pl-6"><span class="sr-only">Foto</span></th>
                            <th scope="col" class="px-3 py-3 font-medium">Nombre</th>
                            <th scope="col" class="px-3 py-3 font-medium">Categoría</th>
                            <th scope="col" class="px-3 py-3 text-right font-medium">Precio</th>
                            <th scope="col" class="px-3 py-3 font-medium">Ingredientes</th>
                            <th scope="col" class="px-3 py-3 text-center font-medium">Destacado</th>
                            <th scope="col" class="px-3 py-3 text-center font-medium">Activo</th>
                            <th scope="col" class="py-3 pr-5 pl-3 text-right font-medium sm:pr-6">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($this->pagina as $producto)
                            <tr wire:key="producto-{{ $producto['id'] }}" class="transition-colors hover:bg-gray-25 dark:hover:bg-white/[0.02]">
                                <td class="py-3 pr-3 pl-5 sm:pl-6">
                                    <span class="flex size-11 items-center justify-center rounded-lg bg-brand-50 text-brand-500 dark:bg-brand-500/10 dark:text-brand-300" aria-hidden="true">
                                        {!! \App\Helpers\MenuHelper::getIconSvg('empanada') !!}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span @class([
                                        'text-sm font-medium',
                                        'text-gray-800 dark:text-white/90' => $producto['activo'],
                                        'text-gray-400 dark:text-gray-500' => ! $producto['activo'],
                                    ])>{{ $producto['nombre'] }}</span>
                                    @unless ($producto['activo'])
                                        <span class="ml-2 rounded-sm bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wider text-gray-500 dark:bg-white/5 dark:text-gray-400">Oculto</span>
                                    @endunless
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $producto['categoria'] }}</td>
                                <td class="px-3 py-3 text-right text-sm font-medium text-gray-800 tabular-nums dark:text-white/90">$ {{ number_format($producto['precio'], 2, ',', '.') }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 tabular-nums dark:text-gray-400">
                                    {{ $producto['ingredientes'] }}
                                    <span class="text-gray-400 dark:text-gray-500">· {{ $producto['extras'] }} {{ $producto['extras'] === 1 ? 'extra' : 'extras' }}</span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <button type="button" wire:click="toggleDestacado({{ $producto['id'] }})"
                                        aria-pressed="{{ $producto['destacado'] ? 'true' : 'false' }}"
                                        aria-label="{{ $producto['destacado'] ? 'Quitar de destacados' : 'Destacar' }}: {{ $producto['nombre'] }}"
                                        @class([
                                            'inline-flex size-9 items-center justify-center rounded-lg transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-500 active:scale-90 dark:hover:bg-white/5',
                                            'text-brand-500 dark:text-brand-400' => $producto['destacado'],
                                            'text-gray-300 hover:text-gray-500 dark:text-gray-600' => ! $producto['destacado'],
                                        ])>
                                        <svg class="size-5" viewBox="0 0 24 24" fill="{{ $producto['destacado'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m12 3.5 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9L12 3.5Z"/>
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <button type="button" role="switch" wire:click="toggleActivo({{ $producto['id'] }})"
                                        aria-checked="{{ $producto['activo'] ? 'true' : 'false' }}"
                                        aria-label="Activo: {{ $producto['nombre'] }}"
                                        @class([
                                            'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
                                            'bg-brand-500' => $producto['activo'],
                                            'bg-gray-200 dark:bg-gray-700' => ! $producto['activo'],
                                        ])>
                                        <span @class([
                                            'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
                                            'translate-x-[22px]' => $producto['activo'],
                                            'translate-x-0.5' => ! $producto['activo'],
                                        ])></span>
                                    </button>
                                </td>
                                <td class="py-3 pr-5 pl-3 text-right sm:pr-6">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button"
                                            class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-gray-300 dark:hover:bg-white/5">
                                            Editar
                                        </button>
                                        <button type="button" wire:click="eliminar({{ $producto['id'] }})"
                                            wire:confirm="¿Eliminar «{{ $producto['nombre'] }}»? Esta acción no se puede deshacer."
                                            class="rounded-md px-2.5 py-1.5 text-sm font-medium text-error-600 transition hover:bg-error-50 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-error-500 dark:hover:bg-error-500/10">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Pie: leyenda y paginación --}}
        <div class="flex flex-col gap-4 border-t border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                <svg class="mr-0.5 -mt-0.5 inline size-3.5 text-brand-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 3.5 2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9L12 3.5Z"/></svg>
                Destacado: aparece en la página de inicio. Inactivo: oculto en la web.
            </p>

            <x-common.pager :page="$page" :total="$this->totalPaginas" />
        </div>
    </section>
</div>
