@php
    $submit = $action === 'store' ? 'store' : 'update';
@endphp

<form wire:submit="{{ $submit }}" class="space-y-8">
    <x-common.page-band :title="$title">
        <x-slot:subtitle>
            {{ $action === 'store' ? 'Las categorías ordenan los productos en la carta.' : 'Cambia el nombre de esta categoría.' }}
        </x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('categories') }}"
                class="rounded-lg px-4 py-2 text-base text-gray-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 dark:text-gray-300">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="{{ $submit }}"
                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] disabled:opacity-60 dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                <span wire:loading.remove wire:target="{{ $submit }}">{{ $action === 'store' ? 'Crear categoría' : 'Guardar cambios' }}</span>
                <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
            </button>
        </x-slot:actions>
    </x-common.page-band>

    <section aria-labelledby="datos-titulo" class="max-w-2xl rounded-xl border border-gray-400/70 p-6 sm:p-8 dark:border-gray-700">
        <h2 id="datos-titulo" class="text-xl font-bold tracking-tight text-gray-800 dark:text-white/90">Datos de la categoría</h2>

        <div class="mt-6">
            <x-form.input name="name" label="Nombre" wire:model="name" required autofocus autocomplete="off" placeholder="Ej. Empanadas de carne" />
        </div>
    </section>
</form>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
