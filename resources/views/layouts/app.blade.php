<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Best Services Assets') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="flex h-screen overflow-hidden bg-gray-50"
             x-data="{ 
                 sidebarOpen: false, 
                 desktopSidebarOpen: true,
                 isMobile: window.innerWidth < 768
             }"
             @resize.window="isMobile = window.innerWidth < 768"
             x-init="$watch('isMobile', value => { if(!value) sidebarOpen = false; })">
            
            <div class="hidden md:flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out z-30 overflow-hidden bg-blue-900"
                 :class="desktopSidebarOpen ? 'w-64' : 'w-0'">
                <div class="w-64 h-full flex-shrink-0">
                    <livewire:layout.navigation />
                </div>
            </div>

            <div class="md:hidden">
                <div 
                    x-show="sidebarOpen" 
                    @click="sidebarOpen = false"
                    class="fixed inset-0 bg-black/50 z-40 transition-opacity"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;"
                ></div>

                <div 
                    x-show="sidebarOpen"
                    class="fixed inset-y-0 left-0 w-64 z-50 transform transition-transform duration-300 shadow-xl bg-blue-900"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    style="display: none;"
                >
                    <livewire:layout.navigation />
                </div>
            </div>

            <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
                <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-20">
                    <div class="flex items-center h-auto px-4 md:px-8 py-4 md:py-6">
                        <button 
                            @click="isMobile ? (sidebarOpen = !sidebarOpen) : (desktopSidebarOpen = !desktopSidebarOpen)"
                            class="inline-flex items-center justify-center p-2 mr-4 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none transition"
                        >
                            <svg x-show="!(isMobile ? sidebarOpen : desktopSidebarOpen)" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <svg x-show="(isMobile ? sidebarOpen : desktopSidebarOpen)" style="display: none;" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <div class="flex-1">
                            @if (isset($header))
                                {{ $header }}
                            @endif
                        </div>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto overflow-x-hidden px-4 md:px-8 py-4 md:py-8 relative z-0">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>