<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-surface">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#131314">

        <title>{{ config('app.name', 'Task Tracker') }}</title>

        @include('partials.favicon')
        @include('partials.fonts')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface text-on-surface">
        <livewire:layout.navigation />

        <div class="min-h-screen flex flex-col sm:pl-20">
            <!-- Page Heading -->
            @if (isset($header))
                <header class="max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-8">
                    {{ $header }}
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 pb-40 sm:pb-28">
                {{ $slot }}
            </main>

            @php
                $appVersion = config('app.version', 'dev');
                $appVersion = preg_match('/^v?\d/', $appVersion) ? 'v'.ltrim($appVersion, 'v') : $appVersion;
            @endphp
            <footer class="hidden sm:block text-center text-body-sm text-outline pb-6">
                &copy; {{ now()->year }} {{ config('app.name', 'Task Tracker') }} &middot; {{ $appVersion }}
            </footer>
        </div>

        <x-bottom-nav />
    </body>
</html>
