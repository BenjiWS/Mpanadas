@props([
    'roles' => null,      // [id => nombre]: el rol se lee en vivo de $wire.role_id
    'role' => null,       // nombre fijo del rol cuando no se puede cambiar (perfil)
    'status' => true,     // muestra el estado leyendo $wire.is_active
])

{{-- Tarjeta de identidad que se actualiza mientras se escribe (lee name, lastname, username y email de $wire) --}}
<article {{ $attributes->class('overflow-hidden rounded-xl border border-gray-300/80 bg-gray-25 dark:border-gray-800 dark:bg-white/[0.03]') }}
    x-data="{
        roles: @js($roles ?? []),
        initials() {
            const letters = [this.$wire.name, this.$wire.lastname].map(part => (part || '').trim().charAt(0)).join('');
            return letters.toUpperCase() || '?';
        },
        fullName() {
            return [this.$wire.name, this.$wire.lastname].map(part => (part || '').trim()).filter(Boolean).join(' ');
        },
    }">
    <div class="px-7 pt-8 pb-7 transition" @if ($status) :class="{ 'opacity-60 grayscale': ! $wire.is_active }" @endif>
        <div class="flex items-center gap-5">
            <span class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-brand-200 text-xl font-bold text-gray-800 dark:bg-brand-500/20 dark:text-brand-200" aria-hidden="true" x-text="initials()"></span>
            <div class="min-w-0">
                <h3 class="truncate text-2xl font-bold tracking-tight text-gray-800 dark:text-white/90"
                    x-text="fullName() || 'Nombre y apellido'" :class="{ 'text-gray-400 dark:text-gray-500': ! fullName() }"></h3>
                <p class="truncate text-sm text-gray-600 dark:text-gray-400" x-text="$wire.username ? '@' + $wire.username : '@usuario'"></p>
            </div>
        </div>

        <dl class="mt-7 space-y-3 border-t border-gray-300/80 pt-5 text-sm dark:border-gray-800">
            <div class="flex items-baseline justify-between gap-4">
                <dt class="text-gray-500 dark:text-gray-400">Correo</dt>
                <dd class="min-w-0 truncate font-medium text-gray-800 dark:text-white/90" x-text="$wire.email || '—'"></dd>
            </div>
            <div class="flex items-baseline justify-between gap-4">
                <dt class="text-gray-500 dark:text-gray-400">Rol</dt>
                <dd>
                    @if ($roles !== null)
                        <span class="rounded-md bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300"
                            x-text="roles[$wire.role_id] || 'Sin rol'"></span>
                    @else
                        <span class="rounded-md bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">{{ $role ?? 'Sin rol' }}</span>
                    @endif
                </dd>
            </div>
            @if ($status)
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-gray-500 dark:text-gray-400">Estado</dt>
                    <dd class="font-medium" :class="$wire.is_active ? 'text-gray-800 dark:text-white/90' : 'text-gray-500 dark:text-gray-400'"
                        x-text="$wire.is_active ? 'Activo' : 'Inactivo · no puede entrar'"></dd>
                </div>
            @endif
        </dl>
    </div>
</article>
