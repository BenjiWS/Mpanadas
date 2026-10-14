@props(['textClass' => ''])

{{-- Logotipo del panel: círculo con la "M" + "EMPANADAS" --}}
<a href="{{ route('dashboard') }}" aria-label="M Empanadas, ir al inicio"
    {{ $attributes->class('flex items-center gap-2.5 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-500') }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-400 text-xl font-medium text-gray-900">M</span>
    <span class="{{ $textClass }} text-[22px] leading-none font-medium tracking-wide text-brand-400 dark:text-brand-300">EMPANADAS</span>
</a>
