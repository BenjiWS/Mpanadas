@php
    $submit = $action === 'store' ? 'store' : 'update';
    $preview = $image ? $image->temporaryUrl() : ($currentImage && ! $removeImage ? Storage::url($currentImage) : null);
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Agrega un producto a la carta.' : 'Actualiza los datos de este producto.' }}
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

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1.8fr)_minmax(0,1fr)] lg:items-start">
        <div class="space-y-7">
            {{-- Datos del producto --}}
            <section aria-labelledby="datos-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
                <h2 id="datos-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Datos del producto</h2>

                <div class="mt-6 space-y-5">
                    <div class="grid gap-5 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                        <x-form.input name="name" label="Nombre" wire:model="name" required autocomplete="off" placeholder="Ej. Empanada de carne" />

                        <div>
                            <label for="price" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Precio<span class="text-error-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400" aria-hidden="true">$</span>
                                <input id="price" type="number" step="0.01" min="0" inputmode="decimal" wire:model="price" placeholder="0,00"
                                    @class([
                                        'shadow-theme-xs h-11 w-full rounded-lg border bg-transparent pr-4 pl-8 text-sm text-gray-800 tabular-nums placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                                        'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('price'),
                                        'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('price'),
                                    ]) />
                            </div>
                            @error('price')
                                <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Categorías --}}
                    <div role="group" aria-labelledby="categorias-label">
                        <p id="categorias-label" class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-400">
                            Categorías<span class="text-error-500">*</span>
                        </p>

                        @if ($categories->isEmpty())
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Todavía no hay categorías.
                                @can('create categories')
                                    <a href="{{ route('categories.create') }}" class="font-medium text-brand-600 underline-offset-2 hover:underline dark:text-brand-300">Crea una primero</a>.
                                @endcan
                            </p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($categories as $category)
                                    <label wire:key="category-{{ $category->id }}"
                                        class="cursor-pointer rounded-full border border-gray-300 px-3.5 py-1.5 text-sm text-gray-700 transition select-none hover:bg-white/60 has-checked:border-brand-500 has-checked:bg-brand-100 has-checked:font-medium has-checked:text-brand-700 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-500 dark:border-gray-700 dark:text-gray-300 dark:has-checked:bg-brand-500/15 dark:has-checked:text-brand-300">
                                        <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="sr-only" />
                                        {{ $category->name }}
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-1.5 text-theme-xs text-gray-500 dark:text-gray-400">Puedes elegir más de una.</p>
                        @endif

                        @error('selectedCategories')
                            <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Ingredientes por defecto --}}
            <section aria-labelledby="default-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h2 id="default-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Ingredientes por defecto</h2>

                    @if ($defaultOptions->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <label for="new-default" class="sr-only">Ingrediente para agregar</label>
                            <select id="new-default" wire:model="newDefaultIngredient"
                                class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Elegir ingrediente…</option>
                                @foreach ($defaultOptions as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="addDefaultIngredient"
                                class="h-10 rounded-lg border border-gray-400/70 bg-white/40 px-3.5 text-sm font-semibold text-gray-800 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98] dark:border-gray-700 dark:bg-transparent dark:text-white/90 dark:hover:bg-white/5">
                                + Agregar
                            </button>
                        </div>
                    @endif
                </div>

                @if (empty($defaultIngredients))
                    <p class="mt-5 text-sm text-gray-600 dark:text-gray-400">
                        @if ($ingredients->isEmpty())
                            Todavía no hay ingredientes.
                            @can('create ingredients')
                                <a href="{{ route('ingredients.create') }}" class="font-medium text-brand-600 underline-offset-2 hover:underline dark:text-brand-300">Crea uno primero</a>.
                            @endcan
                        @else
                            Agrega los ingredientes que lleva este producto.
                        @endif
                    </p>
                @else
                    <div class="custom-scrollbar mt-5 overflow-x-auto">
                        <table class="w-full min-w-[440px] text-left">
                            <thead>
                                <tr class="border-b border-gray-300 text-theme-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    <th scope="col" class="py-2 pr-3 font-medium">Ingrediente</th>
                                    <th scope="col" class="px-3 py-2 font-medium">¿Se puede quitar al vender?</th>
                                    <th scope="col" class="py-2 pl-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach ($defaultIngredients as $index => $row)
                                    @php
                                        $ingredient = $ingredients->get($row['id']);
                                    @endphp
                                    <tr wire:key="default-{{ $row['id'] }}">
                                        <td class="py-2.5 pr-3 text-sm text-gray-800 dark:text-white/90">
                                            {{ $ingredient?->name }}
                                            @if ($ingredient && ! $ingredient->is_active)
                                                <span class="text-theme-xs text-error-600 dark:text-error-500">· desactivado</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <div class="flex items-center gap-4 text-sm text-gray-700 dark:text-gray-300" role="radiogroup" aria-label="¿Se puede quitar {{ $ingredient?->name }}?">
                                                <label class="inline-flex cursor-pointer items-center gap-1.5">
                                                    <input type="radio" wire:model="defaultIngredients.{{ $index }}.removable" value="1" name="removable-{{ $row['id'] }}"
                                                        class="size-4 accent-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500" />
                                                    Quitable
                                                </label>
                                                <label class="inline-flex cursor-pointer items-center gap-1.5">
                                                    <input type="radio" wire:model="defaultIngredients.{{ $index }}.removable" value="0" name="removable-{{ $row['id'] }}"
                                                        class="size-4 accent-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500" />
                                                    Obligatorio
                                                </label>
                                            </div>
                                        </td>
                                        <td class="py-2.5 pl-3 text-right">
                                            <button type="button" wire:click="removeDefaultIngredient({{ $index }})"
                                                aria-label="Quitar {{ $ingredient?->name }} de los ingredientes por defecto"
                                                class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-600 underline-offset-2 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-gray-400 dark:hover:bg-error-500/10">
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <p class="mt-4 text-theme-xs text-gray-500 dark:text-gray-400">Los quitables aparecen al vender para marcar, por ejemplo, «sin cebolla».</p>
            </section>

            {{-- Extras --}}
            <section aria-labelledby="extras-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h2 id="extras-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Ingredientes extra que se pueden agregar</h2>

                    @if ($extraOptions->isNotEmpty())
                        <div class="flex items-center gap-2">
                            <label for="new-extra" class="sr-only">Extra para agregar</label>
                            <select id="new-extra" wire:model="newExtra"
                                class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Elegir extra…</option>
                                @foreach ($extraOptions as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="addExtra"
                                class="h-10 rounded-lg border border-gray-400/70 bg-white/40 px-3.5 text-sm font-semibold text-gray-800 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98] dark:border-gray-700 dark:bg-transparent dark:text-white/90 dark:hover:bg-white/5">
                                + Agregar
                            </button>
                        </div>
                    @endif
                </div>

                @if (empty($extras))
                    <p class="mt-5 text-sm text-gray-600 dark:text-gray-400">Agrega los ingredientes que el cliente puede sumar a este producto.</p>
                @else
                    <div class="custom-scrollbar mt-5 overflow-x-auto">
                        <table class="w-full min-w-[440px] text-left">
                            <thead>
                                <tr class="border-b border-gray-300 text-theme-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    <th scope="col" class="py-2 pr-3 font-medium">Ingrediente extra</th>
                                    <th scope="col" class="px-3 py-2 font-medium">Precio adicional</th>
                                    <th scope="col" class="py-2 pl-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach ($extras as $index => $row)
                                    @php
                                        $ingredient = $ingredients->get($row['id']);
                                    @endphp
                                    <tr wire:key="extra-{{ $row['id'] }}">
                                        <td class="py-2.5 pr-3 text-sm text-gray-800 dark:text-white/90">
                                            {{ $ingredient?->name }}
                                            @if ($ingredient && ! $ingredient->is_active)
                                                <span class="text-theme-xs text-error-600 dark:text-error-500">· desactivado</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <label for="extra-price-{{ $row['id'] }}" class="sr-only">Precio adicional de {{ $ingredient?->name }}</label>
                                            <div class="relative w-32">
                                                <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400" aria-hidden="true">$</span>
                                                <input id="extra-price-{{ $row['id'] }}" type="number" step="0.01" min="0" inputmode="decimal"
                                                    wire:model="extras.{{ $index }}.price"
                                                    placeholder="{{ number_format((float) $ingredient?->extra_price, 2, ',', '') }}"
                                                    @class([
                                                        'h-10 w-full rounded-lg border bg-transparent pr-3 pl-7 text-sm text-gray-800 tabular-nums placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:bg-gray-900 dark:text-white/90',
                                                        'border-error-300 focus:border-error-300 focus:ring-error-500/10 dark:border-error-700' => $errors->has('extras.'.$index.'.price'),
                                                        'border-gray-300 focus:border-brand-300 focus:ring-brand-500/10 dark:border-gray-700' => ! $errors->has('extras.'.$index.'.price'),
                                                    ]) />
                                            </div>
                                            @error('extras.'.$index.'.price')
                                                <p class="mt-1 text-theme-xs text-error-500">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="py-2.5 pl-3 text-right">
                                            <button type="button" wire:click="removeExtra({{ $index }})"
                                                aria-label="Quitar {{ $ingredient?->name }} de los extras"
                                                class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-600 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-gray-400 dark:hover:bg-error-500/10">
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <p class="mt-4 text-theme-xs text-gray-500 dark:text-gray-400">
                    Si dejas el precio vacío se cobra el precio general del módulo Ingredientes. Solo se pueden agregar ingredientes activos.
                </p>
            </section>
        </div>

        <div class="space-y-7">
            {{-- Imagen --}}
            <section aria-labelledby="imagen-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
                <h2 id="imagen-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Imagen</h2>

                <div class="relative mt-6 aspect-[16/10] overflow-hidden rounded-lg bg-brand-50 dark:bg-brand-500/10">
                    @if ($preview)
                        <img src="{{ $preview }}" alt="Vista previa de la imagen del producto" class="size-full object-cover" />
                    @else
                        <div class="flex size-full flex-col items-center justify-center gap-2 text-brand-500 dark:text-brand-300">
                            <span class="[&>svg]:size-10" aria-hidden="true">{!! \App\Helpers\MenuHelper::getIconSvg('empanada') !!}</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Sin imagen</span>
                        </div>
                    @endif

                    <div wire:loading.flex wire:target="image" class="absolute inset-0 items-center justify-center bg-white/70 text-sm font-medium text-gray-700 dark:bg-gray-900/70 dark:text-gray-300">
                        Subiendo…
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <label class="cursor-pointer rounded-lg border border-gray-400/70 bg-white/40 px-4 py-2 text-sm font-semibold text-gray-800 transition hover:bg-white/80 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-brand-500 dark:border-gray-700 dark:bg-transparent dark:text-white/90 dark:hover:bg-white/5">
                        {{ $preview ? 'Cambiar imagen' : 'Subir imagen' }}
                        <input type="file" wire:model="image" accept="image/*" class="sr-only" />
                    </label>
                    @if ($preview)
                        <button type="button" wire:click="clearImage"
                            class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-gray-400 dark:hover:bg-error-500/10">
                            Quitar
                        </button>
                    @endif
                </div>
                <p class="mt-2 text-theme-xs text-gray-500 dark:text-gray-400">JPG, PNG o WebP, hasta 2 MB.</p>
                @error('image')
                    <p class="mt-1.5 text-theme-xs text-error-500">{{ $message }}</p>
                @enderror
            </section>

            {{-- Disponibilidad --}}
            <section aria-labelledby="estado-titulo" class="rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
                <div class="flex items-center justify-between gap-4">
                    <h2 id="estado-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Activo</h2>
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
                <p id="estado-ayuda" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ $available ? 'Se puede vender y aparece en la carta.' : 'Agotado: no se puede vender hasta que lo vuelvas a activar.' }}
                </p>
            </section>
        </div>
    </div>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
