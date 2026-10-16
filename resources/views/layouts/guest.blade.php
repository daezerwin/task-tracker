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
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
            <div class="w-full max-w-md rounded-[28px] bg-surface-container p-8 sm:p-10">
                <x-application-logo class="w-12 h-12" />
                <h1 class="mt-6 text-headline-md text-on-surface">{{ config('app.name', 'Task Tracker') }}</h1>
                <p class="mt-1 text-body-lg text-on-surface-variant">Your household chores, day by day.</p>

                <div class="mt-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
