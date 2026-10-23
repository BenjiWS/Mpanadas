@props(['title'])

{{-- Franja durazno con el título de la página y sus acciones; ocupa todo el ancho del <main> --}}
<div {{ $attributes->class('-mx-4 -mt-4 mb-6 bg-brand-200 px-4 py-6 md:-mx-6 md:-mt-6 md:px-6 md:py-8 dark:bg-brand-500/10') }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-balance text-gray-800 md:text-[2.125rem] md:leading-tight dark:text-white/90">{{ $title }}</h1>
            @isset($subtitle)
                <p class="mt-1 text-sm text-gray-700 tabular-nums dark:text-gray-400">{{ $subtitle }}</p>
            @endisset
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-3">{{ $actions }}</div>
        @endisset
    </div>
</div>
