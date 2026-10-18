<div class="space-y-6">
    <x-common.page-band title="Roles">
        <x-slot:subtitle>Define los accesos de tu equipo.</x-slot:subtitle>
        @can('create roles')
            <x-slot:actions>
                <a href="{{ route('roles.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-base text-brand-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:text-brand-300">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Añadir rol
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    <label class="relative block">
        <span class="sr-only">Buscar un rol</span>
        <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar un rol…" autocomplete="off"
            class="h-11 w-full rounded-lg border border-gray-300 bg-white pr-4 pl-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-gray-500" />
    </label>

    @if ($roles->isEmpty())
        {{-- Estado vacío --}}
        <div class="flex flex-col items-center rounded-xl border border-gray-200 bg-white px-6 py-16 text-center dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                {!! \App\Helpers\MenuHelper::getIconSvg('shield') !!}
            </span>
            @if ($search === '')
                <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Todavía no hay roles</h3>
                <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Crea un rol para agrupar los permisos de cada puesto.</p>
            @else
                <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Ningún rol coincide con «{{ $search }}»</h3>
                <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Prueba con otro nombre.</p>
                <button type="button" wire:click="$set('search', '')"
                    class="mt-4 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Limpiar búsqueda
                </button>
            @endif
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3" wire:loading.class="opacity-60" wire:target="search,gotoPage">
            @foreach ($roles as $role)
                @php($rolePermissions = $role->permissions->countBy('type'))

                <article wire:key="role-{{ $role->id }}" aria-labelledby="role-{{ $role->id }}-name"
                    class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="self-start rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-medium text-brand-700 tabular-nums dark:bg-brand-500/10 dark:text-brand-300">
                        {{ $role->users_count }} {{ $role->users_count === 1 ? 'usuario' : 'usuarios' }}
                    </span>

                    <h2 id="role-{{ $role->id }}-name" class="mt-4 text-lg font-semibold text-gray-800 dark:text-white/90">{{ $role->name }}</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $role->description ?: 'Sin descripción' }}</p>

                    <ul class="mt-5 space-y-3 border-t border-brand-100 pt-5 dark:border-gray-800" aria-label="Módulos con acceso">
                        @foreach ($groups as $type => $permissions)
                            @php($granted = $rolePermissions[$type] ?? 0)
                            <li @class([
                                'flex items-center gap-3 text-sm',
                                'text-gray-800 dark:text-white/90' => $granted > 0,
                                'text-gray-400 dark:text-gray-500' => $granted === 0,
                            ])>
                                @if ($granted > 0)
                                    <svg class="size-4 shrink-0 text-brand-600 dark:text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                                @else
                                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M7 12h10"/></svg>
                                @endif
                                <span class="flex-1">{{ $permissions->first()->type_description }}</span>
                                <span class="text-theme-xs text-gray-500 tabular-nums dark:text-gray-400">
                                    <span class="sr-only">{{ $granted > 0 ? 'Con acceso,' : 'Sin acceso,' }}</span>
                                    {{ $granted }} de {{ $permissions->count() }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    @canany(['edit roles', 'delete roles'])
                        <div class="mt-auto flex items-center justify-between gap-3 pt-6">
                            @can('edit roles')
                                <button type="button" wire:click="edit({{ $role->id }})"
                                    aria-label="Editar permisos de {{ $role->name }}"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-800 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 active:scale-[0.98] dark:border-gray-700 dark:text-white/90 dark:hover:bg-white/5">
                                    Editar permisos
                                </button>
                            @endcan
                            @can('delete roles')
                                <button type="button" wire:click="destroyConfirm({{ $role->id }})"
                                    aria-label="Eliminar el rol {{ $role->name }}"
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-error-600 transition hover:bg-error-50 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-error-500 dark:hover:bg-error-500/10">
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
