@props(['page', 'total', 'action' => 'gotoPage'])

@php
    // Páginas a mostrar: primera, última y las vecinas de la actual; "…" en los huecos
    $paginas = collect([1, $total, $page - 1, $page, $page + 1])
        ->filter(fn ($n) => $n >= 1 && $n <= $total)
        ->unique()->sort()->values();
    $items = [];
    foreach ($paginas as $i => $n) {
        if ($i > 0 && $n - $paginas[$i - 1] > 1) {
            $items[] = null;
        }
        $items[] = $n;
    }
    $link = 'flex h-7 min-w-7 items-center justify-center rounded-md px-1 transition focus-visible:outline-2 focus-visible:outline-brand-500';
@endphp

{{-- Paginador compacto en texto para el pie de las tablas: ‹ 1 2 3 › --}}
@if ($total > 1)
    <nav aria-label="Paginación" {{ $attributes->class('flex items-center gap-0.5 text-sm text-gray-700 tabular-nums dark:text-gray-300') }}>
        <button type="button" wire:click="{{ $action }}({{ $page - 1 }})" @disabled($page <= 1) aria-label="Página anterior"
            class="{{ $link }} hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-40">‹</button>

        @foreach ($items as $n)
            @if ($n === null)
                <span class="flex h-7 min-w-7 items-center justify-center" aria-hidden="true">…</span>
            @else
                <button type="button" wire:click="{{ $action }}({{ $n }})" @if ($n === $page) aria-current="page" @endif
                    @class([$link, 'font-bold text-brand-600 dark:text-brand-300' => $n === $page, 'hover:text-brand-600' => $n !== $page])>{{ $n }}</button>
            @endif
        @endforeach

        <button type="button" wire:click="{{ $action }}({{ $page + 1 }})" @disabled($page >= $total) aria-label="Página siguiente"
            class="{{ $link }} hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-40">›</button>
    </nav>
@endif
