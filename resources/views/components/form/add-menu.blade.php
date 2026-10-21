@props([
    'options',             // colección de ingredientes que se pueden agregar
    'action',              // método de Livewire que recibe el id, p. ej. addExtra
    'label' => 'Agregar',
    'searchLabel' => 'Buscar…',
    'showPrice' => false,  // muestra el precio general junto a cada opción
])

{{-- Botón "+ Agregar" que abre un menú con buscador para elegir una opción; el menú mide lo mismo que el botón --}}
<div class="relative w-40 shrink-0"
    x-data="{
        open: false,
        q: '',
        names: @js($options->pluck('name')->values()),
        matches(name) { return name.toLowerCase().includes(this.q.trim().toLowerCase()); },
        get none() { return ! this.names.some(name => this.matches(name)); },
        toggle() {
            this.open = ! this.open;
            this.q = '';
            if (this.open) this.$nextTick(() => this.$refs.search.focus());
        },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false">
    <button type="button" @click="toggle()" :aria-expanded="open.toString()" aria-haspopup="true"
        @disabled($options->isEmpty())
        title="{{ $options->isEmpty() ? 'No hay más ingredientes activos para agregar' : '' }}"
        class="inline-flex h-11 w-full items-center justify-start gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-800 transition hover:border-brand-400 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:border-gray-300 disabled:hover:text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        {{ $label }}
        <svg class="ml-auto size-3.5 transition" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>

    <div x-show="open" x-cloak x-transition.opacity.scale.origin.top.right
        class="absolute inset-x-0 z-30 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 p-2 dark:border-gray-800">
            <input x-ref="search" x-model="q" type="search" size="1" placeholder="{{ $searchLabel }}" aria-label="{{ $searchLabel }}"
                class="h-9 w-full min-w-0 rounded-lg border-0 bg-gray-50 px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-2 focus:ring-brand-500/20 focus:outline-hidden dark:bg-white/5 dark:text-white/90" />
        </div>

        <ul class="max-h-60 overflow-y-auto p-1">
            @foreach ($options as $option)
                <li wire:key="{{ $action }}-option-{{ $option->id }}" x-show="matches(@js($option->name))">
                    <button type="button" wire:click="{{ $action }}({{ $option->id }})" @click="open = false"
                        class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 focus-visible:bg-brand-50 focus-visible:outline-hidden dark:text-gray-300 dark:hover:bg-brand-500/10 dark:hover:text-brand-300 dark:focus-visible:bg-brand-500/10">
                        <span class="min-w-0 break-words">{{ $option->name }}</span>
                        @if ($showPrice)
                            <span class="shrink-0 text-theme-xs text-gray-500 tabular-nums dark:text-gray-400">$ {{ number_format($option->extra_price, 2, ',', '.') }}</span>
                        @endif
                    </button>
                </li>
            @endforeach
            <li x-show="none" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">Sin coincidencias</li>
        </ul>
    </div>
</div>
