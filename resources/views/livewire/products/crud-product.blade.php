@php
    $submit = $action === 'store' ? 'store' : 'update';
    $preview = $image ? $image->temporaryUrl() : ($currentImage && ! $removeImage ? Storage::url($currentImage) : null);
    $categoryNames = $categories->pluck('name', 'id');

    $sectionNumber = 'flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-sm font-bold text-brand-700 tabular-nums dark:bg-brand-500/15 dark:text-brand-300';
    $sectionTitle = 'text-lg font-bold tracking-tight text-gray-800 dark:text-white/90';
    $countBadge = 'rounded-md bg-gray-100 px-2 py-0.5 text-theme-xs font-semibold text-gray-600 tabular-nums dark:bg-white/5 dark:text-gray-400';
    $addSelect = 'h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:z-10 focus:border-brand-400 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $addButton = 'inline-flex h-10 items-center gap-1.5 rounded-r-lg border border-l-0 border-brand-500 bg-brand-500 px-3.5 text-sm font-semibold text-white transition hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98]';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Arma el producto y mira cómo queda en la carta.' : 'Los cambios se ven en la vista previa antes de guardar.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('products') }}"
                class="rounded-lg px-4 py-2 text-base text-gray-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 dark:text-gray-300">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }},image"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] disabled:opacity-60 dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear producto' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start xl:grid-cols-[minmax(0,1fr)_24rem]">
        {{-- Columna principal: datos del producto e ingredientes --}}
        <div class="@container space-y-8">
            {{-- Formulario --}}
            <div class="divide-y divide-gray-200 overflow-hidden rounded-2xl border border-gray-300/80 bg-white/70 dark:divide-gray-800 dark:border-gray-800 dark:bg-white/[0.03]">

                {{-- 01 · Datos generales --}}
                <section aria-labelledby="datos-titulo" class="p-6 sm:p-8">
                    <div class="flex items-center gap-4">
                        <span class="{{ $sectionNumber }}" aria-hidden="true">01</span>
                        <div>
                            <h2 id="datos-titulo" class="{{ $sectionTitle }}">Datos generales</h2>
                        </div>
                    </div>

                    <div class="mt-6 space-y-6 sm:pl-13">
                        <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_11rem]">
                            <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" placeholder="Ej. Empanada de carne" />

                            <div>
                                <label for="price" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Precio<span class="text-error-500">*</span>
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

                        <div role="group" aria-labelledby="categorias-label" aria-describedby="categorias-ayuda">
                            <div class="mb-2 flex items-baseline justify-between gap-3">
                                <p id="categorias-label" class="text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Categorías<span class="text-error-500">*</span>
                                </p>
                                <p id="categorias-ayuda" class="text-theme-xs text-gray-500 dark:text-gray-400">Puedes elegir más de una</p>
                            </div>

                            @if ($categories->isEmpty())
                                <p class="rounded-lg border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
                                    Todavía no hay categorías.
                                    @can('create categories')
                                        <a href="{{ route('categories.create') }}" class="font-medium text-brand-600 underline-offset-2 hover:underline dark:text-brand-300">Crea una primero</a>.
                                    @endcan
                                </p>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($categories as $category)
                                        <label wire:key="category-{{ $category->id }}"
                                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 transition select-none hover:border-gray-400 active:scale-[0.97] has-checked:border-brand-500 has-checked:bg-brand-50 has-checked:font-medium has-checked:text-brand-700 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-500 dark:border-gray-700 dark:bg-transparent dark:text-gray-300 dark:has-checked:bg-brand-500/15 dark:has-checked:text-brand-300">
                                            <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="peer sr-only" />
                                            <svg class="hidden size-3.5 peer-checked:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                                            {{ $category->name }}
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            @error('selectedCategories')
                                <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            {{-- Ingredientes por defecto y extras, lado a lado cuando hay espacio --}}
            <div class="grid gap-8 @3xl:grid-cols-2 @3xl:items-start">
                <div class="rounded-2xl border border-gray-300/80 bg-white/70 dark:border-gray-800 dark:bg-white/[0.03]">
                    {{-- 02 · Ingredientes por defecto --}}
                    <section aria-labelledby="default-titulo" class="p-6">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-3">
                            <span class="{{ $sectionNumber }}" aria-hidden="true">02</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 id="default-titulo" class="{{ $sectionTitle }} whitespace-nowrap">Ingredientes</h2>
                                    <span class="{{ $countBadge }}">{{ count($defaultIngredients) }}</span>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <x-form.add-menu :options="$defaultOptions" action="addDefaultIngredient" search-label="Buscar…" />
                            </div>
                        </div>

                        <div class="mt-6">
                            @if (empty($defaultIngredients))
                                <div class="rounded-xl border border-dashed border-gray-300 px-5 py-6 text-center dark:border-gray-700">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @if ($ingredients->isEmpty())
                                            Todavía no hay ingredientes.
                                            @can('create ingredients')
                                                <a href="{{ route('ingredients.create') }}" class="font-medium text-brand-600 underline-offset-2 hover:underline dark:text-brand-300">Crea uno primero</a>.
                                            @endcan
                                        @else
                                            Sin ingredientes todavía. Pulsa <span class="font-medium text-gray-800 dark:text-white/90">+ Agregar</span> para elegir uno.
                                        @endif
                                    </p>
                                </div>
                            @else
                                <ul class="space-y-1.5">
                                    @foreach ($defaultIngredients as $index => $row)
                                        @php
                                            $ingredient = $ingredients->get($row['id']);
                                        @endphp
                                        <li wire:key="default-{{ $row['id'] }}" class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 transition hover:border-gray-300 dark:border-gray-800 dark:bg-transparent">
                                            <span class="min-w-20 flex-1 text-sm font-medium text-gray-800 dark:text-white/90">
                                                {{ $ingredient?->name }}
                                                @if ($ingredient && ! $ingredient->is_active)
                                                    <span class="ml-1 text-theme-xs font-normal text-error-600 dark:text-error-500">desactivado</span>
                                                @endif
                                            </span>

                                            {{-- Control segmentado: Quitable / Obligatorio --}}
                                            <div class="inline-flex rounded-lg bg-gray-100 p-0.5 text-theme-xs font-semibold dark:bg-white/5" role="radiogroup" aria-label="¿Se puede quitar {{ $ingredient?->name }} al vender?">
                                                <label class="cursor-pointer rounded-md px-3 py-1.5 text-gray-600 transition select-none has-checked:bg-white has-checked:text-brand-700 has-checked:shadow-theme-xs has-focus-visible:outline-2 has-focus-visible:outline-brand-500 dark:text-gray-400 dark:has-checked:bg-gray-800 dark:has-checked:text-brand-300">
                                                    <input type="radio" wire:model="defaultIngredients.{{ $index }}.removable" value="1" name="removable-{{ $row['id'] }}" class="sr-only" />
                                                    Quitable
                                                </label>
                                                <label class="cursor-pointer rounded-md px-3 py-1.5 text-gray-600 transition select-none has-checked:bg-white has-checked:text-gray-800 has-checked:shadow-theme-xs has-focus-visible:outline-2 has-focus-visible:outline-brand-500 dark:text-gray-400 dark:has-checked:bg-gray-800 dark:has-checked:text-white/90">
                                                    <input type="radio" wire:model="defaultIngredients.{{ $index }}.removable" value="0" name="removable-{{ $row['id'] }}" class="sr-only" />
                                                    Obligatorio
                                                </label>
                                            </div>

                                            <button type="button" wire:click="removeDefaultIngredient({{ $index }})"
                                                aria-label="Quitar {{ $ingredient?->name }} de los ingredientes por defecto" title="Quitar"
                                                class="flex size-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 active:scale-90 dark:hover:bg-error-500/10">
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </section>
                </div>

                <div class="rounded-2xl border border-gray-300/80 bg-white/70 dark:border-gray-800 dark:bg-white/[0.03]">
                    {{-- 03 · Extras --}}
                    <section aria-labelledby="extras-titulo" class="p-6">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-3">
                            <span class="{{ $sectionNumber }}" aria-hidden="true">03</span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 id="extras-titulo" class="{{ $sectionTitle }} whitespace-nowrap">Extras</h2>
                                    <span class="{{ $countBadge }}">{{ count($extras) }}</span>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <x-form.add-menu :options="$extraOptions" action="addExtra" search-label="Buscar…" />
                            </div>
                        </div>

                        <div class="mt-6">
                            @if (empty($extras))
                                <div class="rounded-xl border border-dashed border-gray-300 px-5 py-6 text-center dark:border-gray-700">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Sin extras. Pulsa + Agregar si el cliente puede sumar algo, como queso o huevo.</p>
                                </div>
                            @else
                                <ul class="space-y-1.5">
                                    @foreach ($extras as $index => $row)
                                        @php
                                            $ingredient = $ingredients->get($row['id']);
                                        @endphp
                                        <li wire:key="extra-{{ $row['id'] }}" class="rounded-lg border border-gray-200 bg-white px-3.5 py-2 transition hover:border-gray-300 dark:border-gray-800 dark:bg-transparent">
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                                        {{ $ingredient?->name }}
                                                        @if ($ingredient && ! $ingredient->is_active)
                                                            <span class="ml-1 text-theme-xs font-normal text-error-600 dark:text-error-500">desactivado</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-theme-xs whitespace-nowrap text-gray-500 tabular-nums dark:text-gray-400">General: $ {{ number_format((float) $ingredient?->extra_price, 2, ',', '.') }}</p>
                                                </div>

                                                <div>
                                                    <label for="extra-price-{{ $row['id'] }}" class="sr-only">Precio adicional de {{ $ingredient?->name }} en este producto</label>
                                                    <div class="relative w-32">
                                                        <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400" aria-hidden="true">+ $</span>
                                                        <input id="extra-price-{{ $row['id'] }}" type="number" step="0.01" min="0" inputmode="decimal"
                                                            wire:model="extras.{{ $index }}.price"
                                                            placeholder="{{ number_format((float) $ingredient?->extra_price, 2, ',', '') }}"
                                                            @class([
                                                                'h-9 w-full rounded-lg border bg-transparent pr-3 pl-10 text-sm font-semibold text-gray-800 tabular-nums placeholder:font-normal placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                                                                'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('extras.'.$index.'.price'),
                                                                'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('extras.'.$index.'.price'),
                                                            ]) />
                                                    </div>
                                                </div>

                                                <button type="button" wire:click="removeExtra({{ $index }})"
                                                    aria-label="Quitar {{ $ingredient?->name }} de los extras" title="Quitar"
                                                    class="flex size-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 active:scale-90 dark:hover:bg-error-500/10">
                                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                                                </button>
                                            </div>
                                            @error('extras.'.$index.'.price')
                                                <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                                            @enderror
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>

        {{-- Vista previa en vivo --}}
        <aside aria-labelledby="preview-titulo" class="space-y-4 lg:sticky lg:top-24"
            x-data="{
                categories: @js($categoryNames),
                money(value) {
                    const number = Number(value);
                    return Number.isFinite(number) && value !== '' && value !== null
                        ? number.toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        : '0,00';
                },
                categoryLine() {
                    const names = (this.$wire.selectedCategories || []).map(id => this.categories[id]).filter(Boolean);
                    return names.length ? names.join(' · ') : 'Sin categoría';
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
                            <img src="{{ $preview }}" alt="Foto de {{ $name ?: 'el producto' }}" class="size-full object-cover transition" :class="{ 'opacity-60 grayscale': ! $wire.available }" />
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-gray-900/45 p-3 opacity-0 transition group-hover/photo:opacity-100">
                                <span class="text-sm font-semibold text-white">Arrastra otra foto o haz clic para cambiarla</span>
                            </div>
                        @else
                            <div class="pointer-events-none flex size-full flex-col items-center justify-center gap-2 border-2 border-dashed border-brand-200 text-center transition group-hover/photo:border-brand-400 dark:border-brand-500/30">
                                <span class="text-brand-500 [&>svg]:size-9 dark:text-brand-300" aria-hidden="true">{!! \App\Helpers\MenuHelper::getIconSvg('empanada') !!}</span>
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
                            x-text="$wire.name || 'Nombre del producto'"
                            :class="{ 'text-gray-400 dark:text-gray-500': ! $wire.name }">{{ $name ?: 'Nombre del producto' }}</h3>
                        <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="$wire.available ? 'bg-brand-100 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                            x-text="$wire.available ? 'Activo' : 'Inactivo'">{{ $available ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                    <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-400" x-text="categoryLine()"></p>

                    @if (count($defaultIngredients) || count($extras))
                        <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">
                            {{ count($defaultIngredients) }} {{ count($defaultIngredients) === 1 ? 'ingrediente' : 'ingredientes' }}
                            · {{ count($extras) }} {{ count($extras) === 1 ? 'extra' : 'extras' }}
                        </p>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-3 border-b-4 border-brand-500 bg-brand-200/70 px-5 py-3.5 dark:bg-brand-500/15">
                    <p class="text-2xl font-bold text-gray-800 tabular-nums dark:text-white/90">
                        <span class="sr-only">Precio:</span> $ <span x-text="money($wire.price)">{{ number_format((float) $price, 2, ',', '.') }}</span>
                    </p>
                </div>
            </article>

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
                        {{ $available ? 'Aparece en la carta y en caja.' : 'Agotado: oculto hasta reactivarlo.' }}
                    </p>
                </div>
                <button type="button" role="switch" wire:click="$toggle('available')" aria-labelledby="estado-titulo" aria-describedby="estado-ayuda"
                    aria-checked="{{ $available ? 'true' : 'false' }}"
                    @class([
                        'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
                        'bg-brand-500' => $available,
                        'bg-gray-300 dark:bg-gray-700' => ! $available,
                    ])>
                    <span @class([
                        'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
                        'translate-x-[22px]' => $available,
                        'translate-x-0.5' => ! $available,
                    ])></span>
                </button>
            </div>
        </aside>
    </div>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
