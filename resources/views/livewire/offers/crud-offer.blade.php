@php
    $submit = $action === 'store' ? 'store' : 'update';
    $categoryNames = $categories->pluck('name', 'id');
    $targetNames = collect($selectedCategories)->map(fn ($id) => $categoryNames[$id] ?? null)
        ->merge(collect($selectedProducts)->map(fn ($id) => $products->get($id)?->name))
        ->merge(collect($selectedCombos)->map(fn ($id) => $combos->get($id)?->name))
        ->filter()->values();
    // Oferta sin guardar para mostrar la misma línea de horario o fechas que la tarjeta del listado.
    $draft = new \App\Models\Offer([
        'starts_at' => $starts_at ?: today(),
        'ends_at' => $ends_at ?: today(),
        'weekdays' => $weekdays ? implode(',', $weekdays) : null,
        'start_time' => $start_time ?: null,
        'end_time' => $end_time ?: null,
    ]);
    $scheduleLine = $weekdays || $start_time ? $draft->scheduleLabel() : $draft->periodLabel();

    $sectionNumber = 'flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-sm font-bold text-brand-700 tabular-nums dark:bg-brand-500/15 dark:text-brand-300';
    $sectionTitle = 'text-lg font-bold tracking-tight text-gray-800 dark:text-white/90';
    $fieldLabel = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $field = 'shadow-theme-xs h-11 w-full rounded-lg border bg-transparent px-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90';
    $fieldOk = 'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700';
    $fieldError = 'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700';
    $chip = 'inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 transition select-none hover:border-gray-400 active:scale-[0.97] has-checked:border-brand-500 has-checked:bg-brand-50 has-checked:font-medium has-checked:text-brand-700 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-500 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:has-checked:bg-brand-500/15 dark:has-checked:text-brand-300';
    $presetButton = 'rounded-md px-2 py-1 text-theme-xs font-medium text-brand-600 transition hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-brand-300 dark:hover:bg-brand-500/10';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Arma la promoción y mira cómo queda en la carta.' : 'Los cambios se ven en la vista previa antes de guardar.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('offers') }}"
                class="btn btn-ghost">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}"
                class="btn btn-primary">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear oferta' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start xl:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="@container space-y-8">
            {{-- 01 y 02 en la misma fila cuando la columna mide 64rem o más; las tarjetas quedan de igual alto --}}
            <div class="grid gap-8 @5xl:grid-cols-2">
            {{-- 01 · Datos y descuento --}}
            <section aria-labelledby="datos-titulo" class="@container/section rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-4">
                    <span class="{{ $sectionNumber }}" aria-hidden="true">01</span>
                    <h2 id="datos-titulo" class="{{ $sectionTitle }}">Oferta y descuento</h2>
                </div>

                <div class="mt-6 space-y-6 @2xl/section:pl-13">
                    <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" placeholder="Ej. Tardes con sabor" />

                    <div>
                        <div class="mb-1.5 flex items-baseline justify-between gap-3">
                            <label for="description" class="text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                            <span class="text-theme-xs text-gray-500 dark:text-gray-400">Opcional · si la dejas vacía se muestra a qué aplica</span>
                        </div>
                        <textarea id="description" wire:model="description" rows="2" maxlength="500" placeholder="Ej. Empanadas seleccionadas"
                            @class(['shadow-theme-xs w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30', $fieldError => $errors->has('description'), $fieldOk => ! $errors->has('description')])></textarea>
                        @error('description')
                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 @xl/section:grid-cols-[minmax(0,1fr)_11rem] @xl/section:items-start">
                        <div role="radiogroup" aria-labelledby="tipo-label">
                            <p id="tipo-label" class="{{ $fieldLabel }}">Tipo de descuento<span class="text-error-500">*</span></p>
                            {{-- Control segmentado --}}
                            <div class="inline-flex w-full rounded-lg bg-gray-100 p-1 text-sm font-semibold dark:bg-white/5">
                                @foreach ($discountTypes as $type)
                                    <label class="flex-1 cursor-pointer rounded-md px-3 py-2 text-center whitespace-nowrap text-gray-600 transition select-none has-checked:bg-white has-checked:text-brand-700 has-checked:shadow-theme-xs has-focus-visible:outline-2 has-focus-visible:outline-brand-500 dark:text-gray-400 dark:has-checked:bg-gray-800 dark:has-checked:text-brand-300">
                                        <input type="radio" wire:model.live="discount_type" value="{{ $type->value }}" name="discount_type" class="sr-only" />
                                        {{ $type->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        @if ($discount_type !== 'two_for_one')
                            <div>
                                <label for="discount_value" class="{{ $fieldLabel }}">
                                    {{ $discount_type === 'percentage' ? 'Porcentaje' : 'Monto por unidad' }}<span class="text-error-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-sm font-medium text-gray-500 dark:text-gray-400 {{ $discount_type === 'percentage' ? 'right-4' : 'left-4' }}" aria-hidden="true">{{ $discount_type === 'percentage' ? '%' : '$' }}</span>
                                    <input id="discount_value" type="number" step="0.01" min="0" {{ $discount_type === 'percentage' ? 'max=100' : '' }} inputmode="decimal" wire:model="discount_value" placeholder="{{ $discount_type === 'percentage' ? '10' : '0,00' }}"
                                        @class([$field, 'font-semibold tabular-nums placeholder:font-normal placeholder:text-gray-400', $discount_type === 'percentage' ? 'pr-9' : 'pl-8', $fieldError => $errors->has('discount_value'), $fieldOk => ! $errors->has('discount_value')]) />
                                </div>
                                @error('discount_value')
                                    <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <p class="text-sm text-gray-600 @xl/section:pt-7 dark:text-gray-400">Lleva dos y paga uno: la segunda unidad sale gratis.</p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- 02 · Vigencia y horario --}}
            <section aria-labelledby="vigencia-titulo" class="@container/section rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-4">
                    <span class="{{ $sectionNumber }}" aria-hidden="true">02</span>
                    <h2 id="vigencia-titulo" class="{{ $sectionTitle }}">Vigencia y horario</h2>
                </div>

                <div class="mt-6 space-y-6 @2xl/section:pl-13">
                    <div class="grid gap-5 @sm/section:grid-cols-2">
                        <div>
                            <label for="starts_at" class="{{ $fieldLabel }}">Desde<span class="text-error-500">*</span></label>
                            <input id="starts_at" type="date" wire:model.live="starts_at"
                                @class([$field, $fieldError => $errors->has('starts_at'), $fieldOk => ! $errors->has('starts_at')]) />
                            @error('starts_at')
                                <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="ends_at" class="{{ $fieldLabel }}">Hasta<span class="text-error-500">*</span></label>
                            <input id="ends_at" type="date" wire:model.live="ends_at" min="{{ $starts_at }}"
                                @class([$field, $fieldError => $errors->has('ends_at'), $fieldOk => ! $errors->has('ends_at')]) />
                            @error('ends_at')
                                <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div role="group" aria-labelledby="dias-label">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <p id="dias-label" class="text-sm font-medium text-gray-700 dark:text-gray-400">
                                Días <span class="font-normal text-gray-500 dark:text-gray-400">· {{ \App\Models\Offer::describeWeekdays($weekdays) }}</span>
                            </p>
                            <div class="flex gap-1">
                                <button type="button" wire:click="setWeekdays('all')" class="{{ $presetButton }}">Todos</button>
                                <button type="button" wire:click="setWeekdays('weekdays')" class="{{ $presetButton }}">Lun a vie</button>
                                <button type="button" wire:click="setWeekdays('weekend')" class="{{ $presetButton }}">Fin de semana</button>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach (\App\Models\Offer::WEEKDAYS as $day => [$dayName, $dayShort])
                                <label wire:key="day-{{ $day }}" class="{{ $chip }}" title="{{ $dayName }}">
                                    {{-- wire:model mantiene el estado real de la casilla igual al del servidor (también con los atajos) --}}
                                    <input type="checkbox" wire:model.live="weekdays" value="{{ $day }}" class="sr-only" aria-label="{{ $dayName }}" />
                                    {{ $dayShort }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Sin días marcados, aplica todos los días.</p>
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-baseline justify-between gap-3">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-400">Franja horaria <span class="font-normal text-gray-500 dark:text-gray-400">· opcional</span></p>
                            @if ($start_time || $end_time)
                                <button type="button" wire:click="clearSchedule" class="{{ $presetButton }}">Todo el día</button>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-5 sm:max-w-sm">
                            <div>
                                <label for="start_time" class="sr-only">Desde las</label>
                                <input id="start_time" type="time" wire:model.blur="start_time"
                                    @class([$field, 'tabular-nums', $fieldError => $errors->has('start_time'), $fieldOk => ! $errors->has('start_time')]) />
                            </div>
                            <div>
                                <label for="end_time" class="sr-only">Hasta las</label>
                                <input id="end_time" type="time" wire:model.blur="end_time"
                                    @class([$field, 'tabular-nums', $fieldError => $errors->has('end_time'), $fieldOk => ! $errors->has('end_time')]) />
                            </div>
                        </div>
                        @error('start_time')
                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                        @error('end_time')
                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>
            </div>

            {{-- 03 · A qué aplica: tres listas como las de ingredientes del producto; en columnas si la sección es ancha --}}
            <section aria-labelledby="aplica-titulo" class="@container/aplica rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-4">
                    <span class="{{ $sectionNumber }}" aria-hidden="true">03</span>
                    <div>
                        <h2 id="aplica-titulo" class="{{ $sectionTitle }}">A qué aplica<span class="text-error-500">*</span></h2>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">Categorías completas, productos sueltos o combos</p>
                    </div>
                </div>

                @error('targets')
                    <p class="mt-6 rounded-lg bg-error-50 px-3 py-2 text-sm text-error-600 dark:bg-error-500/10 dark:text-error-500" role="alert">{{ $message }}</p>
                @enderror

                <div class="mt-6 grid gap-8 @4xl/aplica:grid-cols-3 @4xl/aplica:gap-6">
                    @foreach ([
                        ['key' => 'categorias', 'label' => 'Categorías', 'selected' => $selectedCategories, 'items' => $categories, 'options' => $categoryOptions, 'add' => 'addCategory', 'remove' => 'removeCategory', 'none' => 'Ninguna categoría.', 'full' => 'No hay más categorías para agregar'],
                        ['key' => 'productos', 'label' => 'Productos', 'selected' => $selectedProducts, 'items' => $products, 'options' => $productOptions, 'add' => 'addProduct', 'remove' => 'removeProduct', 'none' => 'Ningún producto.', 'full' => 'No hay más productos para agregar'],
                        ['key' => 'combos', 'label' => 'Combos', 'selected' => $selectedCombos, 'items' => $combos, 'options' => $comboOptions, 'add' => 'addCombo', 'remove' => 'removeCombo', 'none' => 'Ningún combo.', 'full' => 'No hay más combos para agregar'],
                    ] as $group)
                        <section aria-labelledby="aplica-{{ $group['key'] }}" class="min-w-0">
                            <div class="flex items-center gap-3">
                                <h3 id="aplica-{{ $group['key'] }}" class="min-w-0 truncate text-base font-bold tracking-tight text-gray-800 dark:text-white/90">{{ $group['label'] }}</h3>
                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-theme-xs font-semibold text-gray-600 tabular-nums dark:bg-white/5 dark:text-gray-400">{{ count($group['selected']) }}</span>
                                <div class="ml-auto">
                                    <x-form.add-menu :options="$group['options']" :action="$group['add']" search-label="Buscar…" :empty-title="$group['full']" compact />
                                </div>
                            </div>

                            <div class="mt-4">
                                @if (empty($group['selected']))
                                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-5 text-center dark:border-gray-700">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $group['none'] }} Pulsa <span class="font-medium text-gray-800 dark:text-white/90">+ Agregar</span> para elegir.</p>
                                    </div>
                                @else
                                    <ul class="space-y-1.5">
                                        @foreach ($group['selected'] as $id)
                                            @php
                                                $item = $group['items']->get($id);
                                                [$meta, $muted] = match ($group['key']) {
                                                    'categorias' => [$item?->products_count.' '.($item?->products_count === 1 ? 'producto' : 'productos'), $item && ! $item->is_active],
                                                    'productos' => ['$ '.number_format((float) $item?->price, 2, ',', '.').($item && ! $item->available ? ' · agotado' : ''), $item && ! $item->available],
                                                    default => ['$ '.number_format((float) $item?->price, 2, ',', '.').($item && ! $item->is_active ? ' · inactivo' : ''), $item && ! $item->is_active],
                                                };
                                            @endphp
                                            <li wire:key="{{ $group['key'] }}-{{ $id }}" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-3.5 py-2 transition hover:border-gray-300 dark:border-gray-800 dark:bg-transparent">
                                                <div class="min-w-0 flex-1">
                                                    <p @class(['truncate text-sm font-medium', 'text-gray-800 dark:text-white/90' => ! $muted, 'text-gray-500 dark:text-gray-400' => $muted])>{{ $item?->name }}</p>
                                                    <p class="text-theme-xs whitespace-nowrap text-gray-500 tabular-nums dark:text-gray-400">{{ $meta }}</p>
                                                </div>
                                                <button type="button" wire:click="{{ $group['remove'] }}({{ $id }})"
                                                    aria-label="Quitar {{ $item?->name }} de la oferta" title="Quitar"
                                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 active:scale-90 dark:hover:bg-error-500/10">
                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </section>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Vista previa en vivo --}}
        <aside aria-labelledby="preview-titulo" class="space-y-4 lg:sticky lg:top-24"
            x-data="{
                headline() {
                    const type = this.$wire.discount_type;
                    if (type === 'two_for_one') return '2 × 1';
                    const value = Number(this.$wire.discount_value);
                    if (! Number.isFinite(value) || this.$wire.discount_value === '' || this.$wire.discount_value === null) return type === 'percentage' ? '0%' : '$ 0,00';
                    return type === 'percentage'
                        ? value.toLocaleString('es', { maximumFractionDigits: 2 }) + '%'
                        : '$ ' + value.toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
            }">
            <div class="flex items-baseline justify-between">
                <h2 id="preview-titulo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Vista previa en la carta</h2>
                <span class="text-theme-xs text-gray-500 dark:text-gray-400">Se actualiza al escribir</span>
            </div>

            <article class="rounded-xl border border-gray-400/70 bg-brand-50 px-7 pt-7 pb-8 dark:border-gray-700 dark:bg-white/[0.03]">
                <p class="text-sm font-semibold text-brand-600 dark:text-brand-300"
                    x-text="$wire.active ? 'Vigente' : 'Pausada'">{{ $active ? 'Vigente' : 'Pausada' }}</p>
                <p class="mt-6 text-7xl leading-none font-light tracking-tight break-words text-brand-600 tabular-nums dark:text-brand-300" x-text="headline()"></p>
                <h3 class="mt-8 text-2xl font-bold tracking-tight break-words text-gray-800 dark:text-white/90"
                    x-text="$wire.name || 'Nombre de la oferta'" :class="{ 'text-gray-400 dark:text-gray-500': ! $wire.name }">{{ $name ?: 'Nombre de la oferta' }}</h3>
                <p class="mt-2 text-lg break-words text-gray-600 dark:text-gray-400"
                    x-text="$wire.description || @js($targetNames->implode(' · ') ?: 'Elige a qué aplica')"></p>
                <p class="mt-5 border-t border-gray-400/70 pt-5 text-sm text-gray-700 tabular-nums dark:border-gray-700 dark:text-gray-300">{{ $scheduleLine }}</p>
            </article>

            {{-- Activa --}}
            <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-300/80 bg-white/70 px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div>
                    <p id="estado-titulo" class="text-sm font-semibold text-gray-800 dark:text-white/90">Oferta activa</p>
                    <p id="estado-ayuda" class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ $active ? 'Se aplica en caja durante su vigencia.' : 'Pausada: no se aplica hasta reactivarla.' }}
                    </p>
                </div>
                <button type="button" role="switch" wire:click="$toggle('active')" aria-labelledby="estado-titulo" aria-describedby="estado-ayuda"
                    aria-checked="{{ $active ? 'true' : 'false' }}"
                    @class([
                        'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
                        'bg-brand-500' => $active,
                        'bg-gray-300 dark:bg-gray-700' => ! $active,
                    ])>
                    <span @class([
                        'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
                        'translate-x-[22px]' => $active,
                        'translate-x-0.5' => ! $active,
                    ])></span>
                </button>
            </div>
        </aside>
    </div>
    @if ($action === 'edit')
        @can('delete offers')
            <x-common.danger-zone title="Eliminar oferta" text="Deja de aplicarse y sale de la lista. Las ventas que ya la usaron la conservan. Para frenarla un tiempo, páusala." button="Eliminar oferta" />
        @endcan
    @endif
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
