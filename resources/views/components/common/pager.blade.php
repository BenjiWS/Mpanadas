@props(['page', 'total', 'action' => 'irAPagina'])

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
@endphp

@if ($total > 1)
    <nav aria-label="Paginación" {{ $attributes->class('inline-flex overflow-hidden rounded-lg bg-brand-300 text-sm font-medium tabular-nums divide-x divide-brand-200 dark:bg-brand-500/20 dark:divide-brand-500/10') }}>
        <button type="button" wire:click="{{ $action }}({{ $page - 1 }})" @disabled($page <= 1) aria-label="Página anterior"
            class="flex h-10 w-11 items-center justify-center text-brand-800 transition hover:bg-brand-400/40 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-brand-700 disabled:cursor-not-allowed disabled:opacity-40 dark:text-brand-300">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6"/></svg>
        </button>

        @foreach ($items as $n)
            @if ($n === null)
                <span class="flex h-10 w-11 items-center justify-center text-gray-800 dark:text-gray-300" aria-hidden="true">…</span>
            @else
                <button type="button" wire:click="{{ $action }}({{ $n }})" @if ($n === $page) aria-current="page" @endif
                    @class([
                        'flex h-10 w-11 items-center justify-center transition focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-brand-700',
                        'bg-brand-600 text-white' => $n === $page,
                        'text-gray-800 hover:bg-brand-400/40 dark:text-gray-300' => $n !== $page,
                    ])>{{ $n }}</button>
            @endif
        @endforeach

        <button type="button" wire:click="{{ $action }}({{ $page + 1 }})" @disabled($page >= $total) aria-label="Página siguiente"
            class="flex h-10 w-11 items-center justify-center text-brand-800 transition hover:bg-brand-400/40 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-brand-700 disabled:cursor-not-allowed disabled:opacity-40 dark:text-brand-300">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
        </button>
    </nav>
@endif
