@props(['id' => 'preview-titulo', 'live' => true])

{{-- Encabezado de la vista previa de un formulario: "Así se ve…" y la nota de que se actualiza al escribir --}}
<div class="flex items-baseline justify-between gap-3">
    <h2 id="{{ $id }}" class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $slot }}</h2>
    @if ($live)
        <span class="text-theme-xs text-gray-500 dark:text-gray-400">Se actualiza al escribir</span>
    @endif
</div>
