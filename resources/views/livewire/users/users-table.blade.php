<div class="space-y-6">
    <x-common.page-band title="Usuarios">
        <x-slot:subtitle>
            {{ $users->total() }} {{ $users->total() === 1 ? 'usuario' : 'usuarios' }}
        </x-slot:subtitle>
        @can('create users')
            <x-slot:actions>
                <a href="{{ route('users.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-base text-brand-700 transition hover:bg-white/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700 active:scale-[0.98] dark:text-brand-300">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Nuevo usuario
                </a>
            </x-slot:actions>
        @endcan
    </x-common.page-band>

    <section aria-labelledby="listado-titulo" class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 px-5 pt-5 pb-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <h2 id="listado-titulo" class="text-lg font-semibold text-gray-800 dark:text-white/90">Listado</h2>

            <label class="relative block sm:w-72">
                <span class="sr-only">Buscar por nombre, username, correo o rol</span>
                <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, username, correo o rol…" autocomplete="off"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent pr-4 pl-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/15 focus:outline-hidden dark:border-gray-700 dark:text-white/90 dark:placeholder:text-gray-500" />
            </label>
        </div>

        @if ($users->isEmpty())
            {{-- Estado vacío --}}
            <div class="flex flex-col items-center border-t border-gray-200 px-6 py-16 text-center dark:border-gray-800">
                <span class="flex size-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    {!! \App\Helpers\MenuHelper::getIconSvg('users') !!}
                </span>
                @if ($search === '')
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Todavía no hay usuarios</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Crea un usuario para cada persona que use el sistema.</p>
                @else
                    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-white/90">Ningún usuario coincide con «{{ $search }}»</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">Prueba con otro nombre, username, correo o rol.</p>
                    <button type="button" wire:click="$set('search', '')"
                        class="mt-4 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Limpiar búsqueda
                    </button>
                @endif
            </div>
        @else
            <div class="custom-scrollbar overflow-x-auto" wire:loading.class="opacity-60" wire:target="search,sortBy,gotoPage">
                <table class="w-full min-w-[980px] text-left">
                    <thead>
                        <tr class="border-y border-gray-200 bg-gray-50 text-theme-xs font-medium text-gray-500 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                            <x-sortable-th field="name" :sort-field="$sortField" :sort-direction="$sortDirection" class="pl-5 sm:pl-6">Nombre</x-sortable-th>
                            <x-sortable-th field="lastname" :sort-field="$sortField" :sort-direction="$sortDirection">Apellido</x-sortable-th>
                            <x-sortable-th field="username" :sort-field="$sortField" :sort-direction="$sortDirection">Username</x-sortable-th>
                            <x-sortable-th field="email" :sort-field="$sortField" :sort-direction="$sortDirection">Correo</x-sortable-th>
                            <th scope="col" class="px-3 py-3 font-medium">Rol</th>
                            <th scope="col" class="px-3 py-3 font-medium">Estado</th>
                            <x-sortable-th field="created_at" :sort-field="$sortField" :sort-direction="$sortDirection">Registrado</x-sortable-th>
                            <th scope="col" class="py-3 pr-5 pl-3 text-right font-medium sm:pr-6">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="transition-colors hover:bg-gray-25 dark:hover:bg-white/[0.02]">
                                <td class="py-3 pr-3 pl-5 sm:pl-6">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300" aria-hidden="true">
                                            {{ $user->initials() }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                            {{ $user->name }}
                                            @if ($user->id === auth()->id())
                                                <span class="ml-1 text-theme-xs font-normal text-gray-500 dark:text-gray-400">(tú)</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-800 dark:text-white/90">{{ $user->lastname }}</td>
                                <td class="px-3 py-3 font-mono text-sm text-gray-600 dark:text-gray-400">{{ $user->username }}</td>
                                <td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</td>
                                <td class="px-3 py-3">
                                    @if ($role = $user->roles->first())
                                        <span class="rounded-sm bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-700 dark:bg-white/5 dark:text-gray-300">{{ $role->name }}</span>
                                    @else
                                        <span class="rounded-sm bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/10 dark:text-error-500">Sin rol</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if ($user->is_active)
                                        <span class="inline-flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="size-1.5 rounded-full bg-success-500" aria-hidden="true"></span> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-sm text-gray-400 dark:text-gray-500">
                                            <span class="size-1.5 rounded-full bg-gray-300 dark:bg-gray-600" aria-hidden="true"></span> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-600 tabular-nums dark:text-gray-400">{{ $user->created_at?->format('d/m/Y') }}</td>
                                <td class="py-3 pr-5 pl-3 text-right sm:pr-6">
                                    <div class="inline-flex items-center gap-1">
                                        @can('edit users')
                                            <button type="button" wire:click="edit({{ $user->id }})"
                                                aria-label="Editar a {{ $user->name }}"
                                                class="rounded-md px-2.5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-500 dark:text-gray-300 dark:hover:bg-white/5">
                                                Editar
                                            </button>
                                        @endcan
                                        @can('delete users')
                                            <button type="button" wire:click="destroyConfirm({{ $user->id }})"
                                                aria-label="Eliminar a {{ $user->name }}"
                                                class="rounded-md px-2.5 py-1.5 text-sm font-medium text-error-600 transition hover:bg-error-50 focus-visible:outline-2 focus-visible:outline-error-500 dark:text-error-500 dark:hover:bg-error-500/10">
                                                Eliminar
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($users->lastPage() > 1)
            <div class="flex justify-end border-t border-gray-200 px-5 py-4 sm:px-6 dark:border-gray-800">
                <x-common.pager :page="$users->currentPage()" :total="$users->lastPage()" action="gotoPage" />
            </div>
        @endif
    </section>
</div>

@push('scripts')
    <x-alerts.sweetalert />
@endpush
