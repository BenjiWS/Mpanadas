@props(['label', 'value', 'hint' => null])

{{-- Indicador sobre una tabla: etiqueta, número grande y una nota corta --}}
<div {{ $attributes->class('rounded-xl border border-gray-400 px-4 py-4 sm:px-6 sm:py-6 dark:border-gray-700') }}>
    <p class="text-xs text-gray-700 sm:text-sm dark:text-gray-400">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold sm:mt-2 sm:text-4xl tracking-tight text-gray-800 tabular-nums dark:text-white/90">{{ $value }}</p>
    @if ($hint)
        <p class="mt-2 hidden text-xs font-medium text-brand-600 sm:block dark:text-brand-300">{{ $hint }}</p>
    @endif
</div>
