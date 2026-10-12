@auth
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-blue-100 shadow-[0_-2px_10px_rgba(0,0,0,0.04)] sm:hidden">
        <div class="grid grid-cols-4 text-center">
            <a href="{{ route('dashboard') }}" wire:navigate
               class="flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('dashboard') ? 'text-blue-700' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-[11px] font-medium">Today</span>
            </a>

            <a href="{{ route('calendar') }}" wire:navigate
               class="flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('calendar') ? 'text-blue-700' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span class="text-[11px] font-medium">Calendar</span>
            </a>

            <a href="{{ route('tasks.index') }}" wire:navigate
               class="flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('tasks.*') ? 'text-blue-700' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <span class="text-[11px] font-medium">Tasks</span>
            </a>

            <a href="{{ route('profile') }}" wire:navigate
               class="flex flex-col items-center gap-0.5 py-2.5 {{ request()->routeIs('profile') ? 'text-blue-700' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span class="text-[11px] font-medium">Profile</span>
            </a>
        </div>
    </nav>
@endauth
