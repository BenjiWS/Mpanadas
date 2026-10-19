<div class="space-y-8">
    <x-common.page-band title="Roles">
        <x-slot:subtitle>Define los accesos de tu equipo.</x-slot:subtitle>
        @can('create roles')
            <x-slot:actions>
                <a href="{{ route('roles.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-white/50 px-5 py-2.5 text-base font-semibold text-brand-600 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:bg-transparent dark:text-brand-300 dark:hover:bg-white/5">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Añadir rol
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    {{-- Buscador y filtros --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <label class="relative block flex-1">
            <span class="sr-only">Buscar un rol</span>
            <svg class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar un rol…" autocomplete="off"
                class="h-12 w-full rounded-xl border border-gray-400/70 bg-transparent pr-4 pl-11 text-base text-gray-800 placeholder:text-gray-500 focus:border-brand-500 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
        </label>

        <div class="flex gap-2" role="group" aria-label="Filtrar roles">
            @foreach (['todos' => 'Todos', 'en_uso' => 'En uso', 'sin_permisos' => 'Sin permisos'] as $value => $label)
                <button type="button" wire:click="setFilter('{{ $value }}')" aria-pressed="{{ $filter === $value ? 'true' : 'false' }}"
                    @class([
                        'h-11 rounded-lg border px-5 text-base whitespace-nowrap transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98]',
                        'border-brand-500 font-semibold text-brand-600 dark:text-brand-300' => $filter === $value,
                        'border-gray-400/70 text-gray-700 hover:bg-white/50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5' => $filter !== $value,
                    ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($roles->isEmpty())
        {{-- Estado vacío --}}
        <div class="flex flex-col items-center rounded-xl border border-gray-400/70 px-6 py-16 text-center dark:border-gray-800">
            <span class="flex size-12 items-center justify-center rounded-xl bg-brand-100 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                {!! \App\Helpers\MenuHelper::getIconSvg('shield') !!}
            </span>
            @if ($search === '' && $filter === 'todos')
                <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Todavía no hay roles</h3>
                <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Crea un rol para agrupar los permisos de cada puesto.</p>
            @else
                <h3 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">Ningún rol coincide</h3>
                <p class="mt-1 max-w-sm text-base text-gray-600 dark:text-gray-400">Prueba con otro nombre o cambia el filtro.</p>
                <button type="button" wire:click="$set('search', ''); setFilter('todos')"
                    class="mt-4 rounded-lg border border-gray-400/70 px-5 py-2.5 text-base font-semibold text-gray-800 transition hover:bg-white/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-white/90 dark:hover:bg-white/5">
                    Ver todos los roles
                </button>
            @endif
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-[repeat(auto-fill,minmax(17rem,21rem))]" wire:loading.class="opacity-60" wire:target="search,setFilter,gotoPage">
            @foreach ($roles as $role)
                @php($types = $role->permissions->pluck('type')->unique())

                <article wire:key="role-{{ $role->id }}" aria-labelledby="role-{{ $role->id }}-name"
                    class="flex flex-col rounded-xl border border-gray-400/70 p-6 dark:border-gray-700">
                    <span class="self-start rounded-full bg-brand-100 px-3 py-1 text-sm font-semibold text-brand-600 tabular-nums dark:bg-brand-500/15 dark:text-brand-300">
                        {{ $role->users_count }} {{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}
                    </span>

                    <h2 id="role-{{ $role->id }}-name" class="mt-5 text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90">{{ $role->name }}</h2>
                    <p class="mt-2 text-base text-gray-700 dark:text-gray-400">{{ $role->description ?: 'Sin descripción' }}</p>

                    <div class="mt-6 border-t border-brand-200 pt-6 dark:border-gray-700">
                        @if ($types->isEmpty())
                            <p class="text-base text-gray-500 dark:text-gray-400">Sin permisos asignados</p>
                        @else
                            <ul class="space-y-4" aria-label="Módulos con acceso">
                                @foreach ($groups as $type => $label)
                                    @if ($types->contains($type))
                                        <li class="flex items-center gap-3 text-base text-gray-800 dark:text-white/90">
                                            <svg class="size-4 shrink-0 text-brand-600 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                                            {{ $label }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    @canany(['edit roles', 'delete roles'])
                        <div class="mt-auto flex items-center justify-between gap-3 pt-8">
                            @can('edit roles')
                                <button type="button" wire:click="edit({{ $role->id }})"
                                    aria-label="Editar permisos de {{ $role->name }}"
                                    class="rounded-lg border border-gray-400/70 bg-white/40 px-6 py-3 text-base font-semibold text-gray-800 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98] dark:border-gray-700 dark:bg-transparent dark:text-white/90 dark:hover:bg-white/5">
                                    Editar permisos
                                </button>
                            @endcan
                            @can('delete roles')
                                <button type="button" wire:click="destroyConfirm({{ $role->id }})"
                                    aria-label="Eliminar el rol {{ $role->name }}"
                                    class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 transition hover:bg-error-50 hover:text-error-600 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-gray-400 dark:hover:bg-error-500/10 dark:hover:text-error-500">
                                    Eliminar
                                </button>
                            @endcan
                        </div>
                    @endcanany
                </article>
            @endforeach
        </div>

        @if ($roles->lastPage() > 1)
            <div class="flex justify-end">
                <x-common.pager :page="$roles->currentPage()" :total="$roles->lastPage()" action="gotoPage" />
            </div>
        @endif
    @endif
</div>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
