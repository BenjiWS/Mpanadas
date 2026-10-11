<div>
    <x-common.page-breadcrumb pageTitle="Contador" />

    <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <span class="text-4xl font-bold text-gray-800 dark:text-white/90">{{ $count }}</span>

        <button wire:click="incrementar" class="rounded-lg bg-brand-500 px-4 py-2 font-medium text-white hover:bg-brand-600">
            +1
        </button>
    </div>
</div>
