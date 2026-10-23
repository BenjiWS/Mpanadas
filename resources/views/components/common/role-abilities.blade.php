@props([
    'abilities',          // salida de DescribesRoleAbilities::roleAbilities()
    'title' => 'Lo que puede hacer',
    'empty' => 'Este rol todavía no tiene permisos.',
])

{{-- Permisos de un rol en lenguaje simple, por módulo --}}
<section aria-labelledby="permisos-rol-titulo" {{ $attributes->class('rounded-xl border border-gray-300/80 bg-white/70 px-6 py-5 dark:border-gray-800 dark:bg-white/[0.03]') }}>
    <h2 id="permisos-rol-titulo" class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
    @if (empty($abilities))
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $empty }}</p>
    @else
        <dl class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
            @foreach ($abilities as $group)
                <div class="flex items-baseline justify-between gap-4 py-2.5">
                    <dt class="font-medium text-gray-800 dark:text-white/90">{{ $group['label'] }}</dt>
                    <dd class="text-right text-gray-600 dark:text-gray-400">{{ ucfirst($group['abilities']) }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</section>
