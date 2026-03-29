<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($header) ? $header . ' | JR' : config('app.name', 'JR') }}</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Reddit+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Material Icons -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer></script>

        <!-- Prevent flash of wrong theme -->
        <script>
            if (localStorage.getItem('jr-theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-mono-50"
          x-data="{
              sidebarOpen: window.innerWidth >= 768,
              darkMode: localStorage.getItem('jr-theme') === 'dark',
              toggleDark() {
                  this.darkMode = !this.darkMode;
                  localStorage.setItem('jr-theme', this.darkMode ? 'dark' : 'light');
              }
          }"
          :data-theme="darkMode ? 'dark' : ''"
    >
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-w-0 transition-[margin] duration-300" :class="sidebarOpen ? 'md:ml-60' : 'ml-0 md:ml-16'">
                <!-- Header -->
                @include('layouts.header')

                <!-- Page Content -->
                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen"
             x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/30 z-40 md:hidden">
        </div>

    </body>
</html>
