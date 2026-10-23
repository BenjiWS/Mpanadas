@php
    $submit = $action === 'store' ? 'store' : 'update';
    $preview = $image ? $image->temporaryUrl() : ($currentImage && ! $removeImage ? Storage::url($currentImage) : null);
    $productInfo = $products->map(fn ($product) => ['name' => $product->name, 'price' => (float) $product->price]);

    $sectionNumber = 'flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-sm font-bold text-brand-700 tabular-nums dark:bg-brand-500/15 dark:text-brand-300';
    $sectionTitle = 'text-lg font-bold tracking-tight text-gray-800 dark:text-white/90';
    $countBadge = 'rounded-md bg-gray-100 px-2 py-0.5 text-theme-xs font-semibold text-gray-600 tabular-nums dark:bg-white/5 dark:text-gray-400';
    $stepButton = 'flex size-8 items-center justify-center text-gray-600 transition hover:bg-gray-100 hover:text-gray-800 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-brand-500 active:scale-90 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-400 dark:hover:bg-white/5';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Arma el combo y mira cómo queda en la carta.' : 'Los cambios se ven en la vista previa antes de guardar.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('combos') }}"
                class="btn btn-ghost">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }},image"
                class="btn btn-primary">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear combo' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start xl:grid-cols-[minmax(0,1fr)_24rem]">
        {{-- Columna principal: datos del combo y productos que trae --}}
        <div class="space-y-8">
            {{-- 01 · Datos generales --}}
            <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-4">
                    <span class="{{ $sectionNumber }}" aria-hidden="true">01</span>
                    <h2 id="datos-titulo" class="{{ $sectionTitle }}">Datos generales</h2>
                </div>

                <div class="mt-6 space-y-6 sm:pl-13">
                    <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_11rem]">
                        <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" placeholder="Ej. Combo clásico" />

                        <div>
                            <label for="price" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Precio del combo<span class="text-error-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sm font-medium text-gray-500 dark:text-gray-400" aria-hidden="true">$</span>
                                <input id="price" type="number" step="0.01" min="0" inputmode="decimal" wire:model="price" placeholder="0,00"
                                    @class([
                                        'shadow-theme-xs h-11 w-full rounded-lg border bg-transparent pr-4 pl-8 text-sm font-semibold text-gray-800 tabular-nums placeholder:font-normal placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                                        'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('price'),
                                        'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('price'),
                                    ]) />
                            </div>
                            @error('price')
                                <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="mb-1.5 flex items-baseline justify-between gap-3">
                            <label for="description" class="text-sm font-medium text-gray-700 dark:text-gray-400">Descripción</label>
                            <span class="text-theme-xs text-gray-500 dark:text-gray-400">Opcional · si la dejas vacía se muestra lo que trae</span>
                        </div>
                        <textarea id="description" wire:model="description" rows="2" maxlength="500" placeholder="Ej. Empanadas + bebida"
                            @class([
                                'shadow-theme-xs w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                                'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('description'),
                                'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('description'),
                            ])></textarea>
                        @error('description')
                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- 02 · Productos del combo --}}
            <section aria-labelledby="productos-titulo" class="rounded-xl border border-gray-300/80 bg-white/70 p-6 sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-3">
                    <span class="{{ $sectionNumber }}" aria-hidden="true">02</span>
                    <div class="flex items-center gap-2">
                        <h2 id="productos-titulo" class="{{ $sectionTitle }} whitespace-nowrap">Productos<span class="text-error-500">*</span></h2>
                        <span class="{{ $countBadge }}">{{ count($items) }}</span>
                    </div>
                    <div class="ml-auto">
                        <x-form.add-menu :options="$productOptions" action="addItem" search-label="Buscar producto…"
                            empty-title="No hay más productos disponibles para agregar" />
                    </div>
                </div>

                <div class="mt-6 sm:pl-13">
                    @if (empty($items))
                        <div @class([
                            'rounded-xl border border-dashed px-5 py-6 text-center',
                            'border-error-300 dark:border-error-700' => $errors->has('items'),
                            'border-gray-300 dark:border-gray-700' => ! $errors->has('items'),
                        ])>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @if ($products->isEmpty())
                                    Todavía no hay productos disponibles.
                                    @can('create products')
                                        <a href="{{ route('products.create') }}" class="font-medium text-brand-600 underline-offset-2 hover:underline dark:text-brand-300">Crea uno primero</a>.
                                    @endcan
                                @else
                                    Sin productos todavía. Pulsa <span class="font-medium text-gray-800 dark:text-white/90">+ Agregar</span> para elegir lo que trae el combo.
                                @endif
                            </p>
                        </div>
                    @else
                        <ul class="space-y-1.5">
                            @foreach ($items as $index => $row)
                                @php
                                    $product = $products->get($row['id']);
                                @endphp
                                <li wire:key="item-{{ $row['id'] }}" class="rounded-lg border border-gray-200 bg-white px-3.5 py-2 transition hover:border-gray-300 dark:border-gray-800 dark:bg-transparent">
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                                {{ $product?->name }}
                                                @if ($product && ! $product->available)
                                                    <span class="ml-1 text-theme-xs font-normal text-error-600 dark:text-error-500">agotado</span>
                                                @endif
                                            </p>
                                            <p class="text-theme-xs whitespace-nowrap text-gray-500 tabular-nums dark:text-gray-400">$ {{ number_format((float) $product?->price, 2, ',', '.') }} c/u</p>
                                        </div>

                                        {{-- Cantidad --}}
                                        <div class="inline-flex items-center overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700" role="group" aria-label="Cantidad de {{ $product?->name }}">
                                            <button type="button" wire:click="changeQuantity({{ $index }}, -1)" @disabled((int) $row['quantity'] <= 1)
                                                aria-label="Quitar una unidad" class="{{ $stepButton }}">
                                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>
                                            </button>
                                            <label for="quantity-{{ $row['id'] }}" class="sr-only">Cantidad de {{ $product?->name }}</label>
                                            <input id="quantity-{{ $row['id'] }}" type="number" min="1" max="999" step="1" inputmode="numeric"
                                                wire:model.blur="items.{{ $index }}.quantity"
                                                class="h-8 w-12 border-x border-gray-300 bg-transparent text-center text-sm font-semibold text-gray-800 tabular-nums [appearance:textfield] focus:outline-hidden dark:border-gray-700 dark:text-white/90 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                                            <button type="button" wire:click="changeQuantity({{ $index }}, 1)"
                                                aria-label="Agregar una unidad" class="{{ $stepButton }}">
                                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        </div>

                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            aria-label="Quitar {{ $product?->name }} del combo" title="Quitar"
                                            class="flex size-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 active:scale-90 dark:hover:bg-error-500/10">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                                        </button>
                                    </div>
                                    @error('items.'.$index.'.quantity')
                                        <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                                    @enderror
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @error('items')
                        <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </section>
        </div>

        {{-- Vista previa en vivo --}}
        <aside aria-labelledby="preview-titulo" class="space-y-4 lg:sticky lg:top-24"
            x-data="{
                products: @js($productInfo),
                money(value) {
                    const number = Number(value);
                    return Number.isFinite(number) && value !== '' && value !== null
                        ? number.toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        : '0,00';
                },
                rows() {
                    return (this.$wire.items || []).filter(row => this.products[row.id]);
                },
                contents() {
                    return this.rows().map(row => `${row.quantity} ${this.products[row.id].name}`).join(' + ');
                },
                separate() {
                    return this.rows().reduce((sum, row) => sum + this.products[row.id].price * (Number(row.quantity) || 0), 0);
                },
                savings() {
                    return this.separate() - (Number(this.$wire.price) || 0);
                },
            }">
            <div class="flex items-baseline justify-between">
                <h2 id="preview-titulo" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Vista previa en la carta</h2>
                <span class="text-theme-xs text-gray-500 dark:text-gray-400">Se actualiza al escribir</span>
            </div>

            <article class="overflow-hidden rounded-xl border border-gray-400/70 bg-white/40 dark:border-gray-700 dark:bg-transparent">
                <div class="p-5">
                    {{-- Foto: se arrastra o se elige sobre la imagen --}}
                    <div class="group/photo relative aspect-[16/10] overflow-hidden rounded-lg bg-brand-50 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-500 dark:bg-brand-500/10">
                        @if ($preview)
                            <img src="{{ $preview }}" alt="Foto de {{ $name ?: 'el combo' }}" class="size-full object-cover transition" :class="{ 'opacity-60 grayscale': ! $wire.is_active }" />
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-gray-900/45 p-3 opacity-0 transition group-hover/photo:opacity-100">
                                <span class="text-sm font-semibold text-white">Arrastra otra foto o haz clic para cambiarla</span>
                            </div>
                        @else
                            <div class="pointer-events-none flex size-full flex-col items-center justify-center gap-2 border-2 border-dashed border-brand-200 text-center transition group-hover/photo:border-brand-400 dark:border-brand-500/30">
                                <span class="text-brand-500 [&>svg]:size-9 dark:text-brand-300" aria-hidden="true">{!! \App\Helpers\MenuHelper::getIconSvg('combo') !!}</span>
                                <span class="text-sm font-semibold text-gray-800 dark:text-white/90">Arrastra una foto aquí</span>
                                <span class="text-theme-xs text-gray-500 dark:text-gray-400">o haz clic para elegirla · hasta 2 MB</span>
                            </div>
                        @endif

                        <input type="file" wire:model="image" accept="image/*" aria-label="{{ $preview ? 'Cambiar foto' : 'Subir foto' }}"
                            class="absolute inset-0 size-full cursor-pointer opacity-0" />

                        <div wire:loading.flex wire:target="image" class="absolute inset-0 items-center justify-center bg-white/80 text-sm font-medium text-gray-700 dark:bg-gray-900/80 dark:text-gray-300">
                            Subiendo foto…
                        </div>
                    </div>

                    <div class="mt-4 flex items-start justify-between gap-3">
                        <h3 class="min-w-0 text-xl font-bold tracking-tight break-words text-gray-800 dark:text-white/90"
                            x-text="$wire.name || 'Nombre del combo'"
                            :class="{ 'text-gray-400 dark:text-gray-500': ! $wire.name }">{{ $name ?: 'Nombre del combo' }}</h3>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="$wire.is_active ? 'bg-brand-100 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                            x-text="$wire.is_active ? 'Activo' : 'Inactivo'">{{ $is_active ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                    <p class="mt-1.5 text-sm break-words text-gray-600 dark:text-gray-400" x-text="$wire.description || contents() || 'Sin productos'"></p>
                </div>

                <div class="flex items-center justify-between gap-3 border-b-4 border-brand-500 bg-brand-200/70 px-5 py-3.5 dark:bg-brand-500/15">
                    <p class="text-2xl font-bold text-gray-800 tabular-nums dark:text-white/90">
                        <span class="sr-only">Precio:</span> $ <span x-text="money($wire.price)">{{ number_format((float) $price, 2, ',', '.') }}</span>
                    </p>
                </div>
            </article>

            {{-- Comparación con el precio por separado --}}
            <div x-show="rows().length" x-cloak class="rounded-xl border border-gray-300/80 bg-white/70 px-5 py-4 text-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-baseline justify-between gap-3">
                    <span class="text-gray-600 dark:text-gray-400">Por separado</span>
                    <span class="font-semibold text-gray-800 tabular-nums dark:text-white/90">$ <span x-text="money(separate())"></span></span>
                </div>
                <div class="mt-1 flex items-baseline justify-between gap-3">
                    <span class="text-gray-600 dark:text-gray-400" x-text="savings() >= 0 ? 'El cliente ahorra' : 'El combo cuesta más'"></span>
                    <span class="font-semibold tabular-nums" :class="savings() >= 0 ? 'text-brand-600 dark:text-brand-300' : 'text-error-600 dark:text-error-500'">
                        $ <span x-text="money(Math.abs(savings()))"></span>
                    </span>
                </div>
            </div>

            @error('image')
                <p class="text-theme-xs text-error-500">{{ $message }}</p>
            @enderror
            @if ($preview)
                <button type="button" wire:click="clearImage"
                    class="text-sm font-medium text-gray-500 underline-offset-2 transition hover:text-error-600 hover:underline focus-visible:outline-2 focus-visible:outline-error-500 dark:text-gray-400">
                    Quitar foto
                </button>
            @endif

            {{-- Activo --}}
            <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-300/80 bg-white/70 px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div>
                    <p id="estado-titulo" class="text-sm font-semibold text-gray-800 dark:text-white/90">Disponible para vender</p>
                    <p id="estado-ayuda" class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">
                        {{ $is_active ? 'Aparece en la carta y en caja.' : 'Inactivo: oculto hasta reactivarlo.' }}
                    </p>
                </div>
                <button type="button" role="switch" wire:click="$toggle('is_active')" aria-labelledby="estado-titulo" aria-describedby="estado-ayuda"
                    aria-checked="{{ $is_active ? 'true' : 'false' }}"
                    @class([
                        'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
                        'bg-brand-500' => $is_active,
                        'bg-gray-300 dark:bg-gray-700' => ! $is_active,
                    ])>
                    <span @class([
                        'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
                        'translate-x-[22px]' => $is_active,
                        'translate-x-0.5' => ! $is_active,
                    ])></span>
                </button>
            </div>
        </aside>
    </div>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
