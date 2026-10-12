<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Task Tracker') }}</title>

        @include('partials.favicon')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-blue-50/40 text-gray-800">
        <div class="min-h-screen flex flex-col">
            <header class="max-w-5xl w-full mx-auto px-6 py-8 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-application-logo class="w-9 h-9" />
                    <span class="text-xl font-semibold text-gray-800">Task Tracker</span>
                </div>

                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}">
                            <x-secondary-button type="button">Today</x-secondary-button>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-base font-medium text-gray-600 hover:text-blue-700 px-3 py-2">
                            Log in
                        </a>
                        <a href="{{ route('register') }}">
                            <x-primary-button type="button">Get Started</x-primary-button>
                        </a>
                    @endauth
                </nav>
            </header>

            <main class="flex-1 flex items-center">
                <div class="max-w-5xl w-full mx-auto px-6 py-12 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 leading-tight">
                            Every chore, on its own schedule.
                        </h1>
                        <p class="mt-5 text-lg text-gray-600">
                            See today's household tasks at a glance and tick them off as you go. Laundry twice a week,
                            bills on the 10th, the bathroom every Saturday — set it once and it comes round on its own.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}">
                                    <x-primary-button type="button">Go to Today</x-primary-button>
                                </a>
                            @else
                                <a href="{{ route('register') }}">
                                    <x-primary-button type="button">Create your account</x-primary-button>
                                </a>
                                <a href="{{ route('login') }}">
                                    <x-secondary-button type="button">Log in</x-secondary-button>
                                </a>
                            @endauth
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <x-card class="p-8">
                            <p class="text-base font-semibold text-emerald-600">Daily Checklist</p>
                            <p class="text-base text-gray-500 mt-2">Today's chores, one tap to tick off.</p>
                        </x-card>
                        <x-card class="p-8">
                            <p class="text-base font-semibold text-blue-700">Repeating Tasks</p>
                            <p class="text-base text-gray-500 mt-2">Daily, weekly, monthly — or every few.</p>
                        </x-card>
                        <x-card class="p-8">
                            <p class="text-base font-semibold text-violet-600">Calendar</p>
                            <p class="text-base text-gray-500 mt-2">See the whole month and what got done.</p>
                        </x-card>
                        <x-card class="p-8">
                            <p class="text-base font-semibold text-amber-600">Starter Chores</p>
                            <p class="text-base text-gray-500 mt-2">Cleaning, laundry and more, ready to go.</p>
                        </x-card>
                    </div>
                </div>
            </main>

            <footer class="max-w-5xl w-full mx-auto px-6 py-6 text-center text-sm text-gray-400">
                Keeping the house running, one tick at a time.
            </footer>
        </div>
    </body>
</html>
