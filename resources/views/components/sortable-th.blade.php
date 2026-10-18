@props(['field', 'sortField', 'sortDirection'])

@php
    $active = $sortField === $field;
@endphp

{{-- Encabezado de tabla que ordena por columna; se usa con el trait WithSorting --}}
<th scope="col" aria-sort="{{ $active ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" {{ $attributes->class('px-3 py-3 font-medium') }}>
    <button type="button" wire:click="sortBy('{{ $field }}')"
        class="group inline-flex items-center gap-1.5 rounded-sm hover:text-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:hover:text-white/90">
        {{ $slot }}
        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m8 9 4-4 4 4" @class(['text-gray-800 dark:text-white/90' => $active && $sortDirection === 'asc', 'opacity-40 group-hover:opacity-70' => ! ($active && $sortDirection === 'asc')]) />
            <path d="m8 15 4 4 4-4" @class(['text-gray-800 dark:text-white/90' => $active && $sortDirection === 'desc', 'opacity-40 group-hover:opacity-70' => ! ($active && $sortDirection === 'desc')]) />
        </svg>
    </button>
</th>
