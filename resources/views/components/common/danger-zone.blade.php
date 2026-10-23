@props(['title', 'text', 'button' => 'Eliminar', 'action' => 'destroyConfirm'])

{{-- Acción destructiva al final de un formulario de edición, lejos de Guardar y Cancelar --}}
<section aria-labelledby="zona-peligro-titulo"
    {{ $attributes->class('flex flex-col gap-4 rounded-xl border border-error-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-error-500/30') }}>
    <div>
        <h2 id="zona-peligro-titulo" class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <p class="mt-1 max-w-prose text-sm text-gray-600 dark:text-gray-400">{{ $text }}</p>
    </div>
    <button type="button" wire:click="{{ $action }}" class="btn btn-danger shrink-0">{{ $button }}</button>
</section>
