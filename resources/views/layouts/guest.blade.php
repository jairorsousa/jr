<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JR') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Reddit+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-mono-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-mono-50">
            <!-- Logo -->
            <div class="mb-6">
                <a href="/" class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary-500 flex items-center justify-center">
                        <span class="text-white font-bold text-xl">JR</span>
                    </div>
                    <span class="text-2xl font-bold text-mono-900">Sistema JR</span>
                </a>
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md px-8 py-8 bg-white shadow-card border border-mono-100 rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
