<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" class="h-full bg-gray-50 dark:bg-gray-900">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Dashboard' }} | {{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        @include('layouts.partials.theme')

        {{-- Store de Alpine para abrir/cerrar el sidebar --}}
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    isExpanded: false,
                    isMobileOpen: false,
                    isHovered: false,

                    init() {
                        const savedState = localStorage.getItem('sidebarExpanded');
                        this.isExpanded = window.innerWidth >= 1280 && savedState !== 'false';

                        window.addEventListener('resize', () => {
                            this.isMobileOpen = false;
                            if (window.innerWidth >= 1280) {
                                this.isExpanded = localStorage.getItem('sidebarExpanded') !== 'false';
                            }
                        });
                    },
                    toggleExpanded() {
                        this.isExpanded = ! this.isExpanded;
                        this.isMobileOpen = false;

                        if (window.innerWidth >= 1280) {
                            localStorage.setItem('sidebarExpanded', this.isExpanded);
                        }
                    },
                    toggleMobileOpen() {
                        this.isMobileOpen = ! this.isMobileOpen;
                    },
                    setMobileOpen(val) {
                        this.isMobileOpen = val;
                    },
                    setHovered(val) {
                        if (window.innerWidth >= 1280 && ! this.isExpanded) {
                            this.isHovered = val;
                        }
                    },
                });
            });
        </script>
    </head>
    <body>
        <div class="min-h-screen xl:flex sidebar-expanded" x-data :class="{ 'sidebar-expanded': $store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen }">
            @include('layouts.backdrop')
            @include('layouts.sidebar')

            <div class="flex-1 ml-0 xl:ml-[90px] [.sidebar-expanded_&]:xl:ml-[290px] transition-all duration-300 ease-in-out">
                @include('layouts.app-header')

                <main class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
