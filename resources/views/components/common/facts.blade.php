@props(['items']) {{-- [['Etiqueta', valor], ...] --}}

{{-- Datos cortos en una grilla de dos columnas (conteos, fechas) --}}
<dl {{ $attributes->class('grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-gray-300/80 bg-gray-300/80 text-sm dark:border-gray-800 dark:bg-gray-800') }}>
    @foreach ($items as [$label, $value])
        <div @class(['bg-gray-25 px-5 py-4 dark:bg-gray-900', 'col-span-2' => $loop->last && $loop->count % 2 === 1])>
            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt>
            <dd class="mt-1 font-semibold text-gray-800 tabular-nums dark:text-white/90">{{ $value }}</dd>
        </div>
    @endforeach
</dl>
