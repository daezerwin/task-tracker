<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Task Tracker') }}</title>

        @include('partials.favicon')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-blue-50/40">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white/70 backdrop-blur border-b border-blue-100">
                    <div class="max-w-5xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="pb-24 sm:pb-8">
                {{ $slot }}
            </main>

            @php
                $appVersion = config('app.version', 'dev');
                $appVersion = preg_match('/^v?\d/', $appVersion) ? 'v'.ltrim($appVersion, 'v') : $appVersion;
            @endphp
            <footer class="text-center text-xs text-gray-400 pb-24 sm:pb-6">
                &copy; {{ now()->year }} {{ config('app.name', 'Task Tracker') }} &middot; {{ $appVersion }}
            </footer>

            <x-bottom-nav />
        </div>
    </body>
</html>
