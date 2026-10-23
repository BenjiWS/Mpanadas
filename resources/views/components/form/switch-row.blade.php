@props([
    'id',                 // prefijo de los ids del título y la ayuda
    'title',
    'help' => null,
    'model',              // propiedad de Livewire que se alterna con $toggle
    'checked' => false,
    'disabled' => false,
])

{{-- Fila con título, ayuda e interruptor (role="switch") que alterna una propiedad booleana de Livewire --}}
<div {{ $attributes->class('flex items-center justify-between gap-6') }}>
    <div>
        <p id="{{ $id }}-titulo" class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $title }}</p>
        @if ($help)
            <p id="{{ $id }}-ayuda" class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">{{ $help }}</p>
        @endif
    </div>
    <button type="button" role="switch" wire:click="$toggle('{{ $model }}')" @disabled($disabled)
        aria-labelledby="{{ $id }}-titulo" @if ($help) aria-describedby="{{ $id }}-ayuda" @endif
        aria-checked="{{ $checked ? 'true' : 'false' }}"
        @class([
            'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 disabled:cursor-not-allowed disabled:opacity-50',
            'bg-brand-500' => $checked,
            'bg-gray-300 dark:bg-gray-700' => ! $checked,
        ])>
        <span @class([
            'inline-block size-5 rounded-full bg-white shadow-theme-xs transition-transform duration-200',
            'translate-x-[22px]' => $checked,
            'translate-x-0.5' => ! $checked,
        ])></span>
    </button>
</div>
