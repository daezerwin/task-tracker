@auth
    @php
        $destinations = [
            ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'task_alt', 'label' => 'Today'],
            ['route' => 'calendar', 'match' => 'calendar', 'icon' => 'calendar_month', 'label' => 'Calendar'],
            ['route' => 'tasks.index', 'match' => 'tasks.*', 'icon' => 'checklist', 'label' => 'Tasks'],
            ['route' => 'profile', 'match' => 'profile', 'icon' => 'person', 'label' => 'Profile'],
        ];
    @endphp

    <!-- Material navigation bar (phones) -->
    <nav class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-surface-container pb-[env(safe-area-inset-bottom)]" aria-label="Main">
        <div class="grid h-20 grid-cols-4">
            @foreach ($destinations as $item)
                @php($active = request()->routeIs($item['match']))
                <a href="{{ route($item['route']) }}" wire:navigate class="group flex flex-col items-center justify-center gap-1" @if ($active) aria-current="page" @endif>
                    <span class="flex h-8 w-16 items-center justify-center rounded-full transition {{ $active ? 'bg-secondary-container text-on-secondary-container' : 'text-on-surface-variant group-active:bg-on-surface/[0.12]' }}">
                        <span class="icon {{ $active ? 'icon-filled' : '' }}">{{ $item['icon'] }}</span>
                    </span>
                    <span class="text-label-md {{ $active ? 'text-on-surface' : 'text-on-surface-variant' }}">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
@endauth
