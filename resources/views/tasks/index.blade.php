<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tasks" subtitle="Your chores and how often they repeat.">
            <x-slot name="actions">
                <a href="{{ route('tasks.create') }}" wire:navigate><x-primary-button>+ Add</x-primary-button></a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-700 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        @if (! $hasTasks)
            <x-card class="p-7">
                <x-empty-state title="No tasks yet" subtitle="Add your own, or start with a list of common household chores already on a schedule.">
                    <x-slot name="action">
                        <form method="POST" action="{{ route('tasks.starter') }}">
                            @csrf
                            <x-primary-button>Add starter chores</x-primary-button>
                        </form>
                    </x-slot>
                </x-empty-state>
            </x-card>
        @else
            <div class="space-y-6">
                @foreach ($groups as $group)
                    <div>
                        <div class="flex items-center gap-2 mb-2 px-1">
                            <span class="w-2.5 h-2.5 rounded-full {{ $group['category']->dotClass() }}"></span>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                {{ $group['category']->label() }} &middot; {{ $group['tasks']->count() }}
                            </p>
                        </div>
                        <x-card class="px-7 py-2">
                            <ul class="divide-y divide-gray-100">
                                @foreach ($group['tasks'] as $task)
                                    @php($next = $task->nextOccurrence())
                                    <x-timeline-item
                                        :title="$task->title.($task->is_active ? '' : ' · Paused')"
                                        :time="$task->scheduleLabel().($task->timeLabel() ? ' · '.$task->timeLabel() : '')"
                                        :meta="$task->is_active && $task->isRepeating() ? ($next ? 'Next: '.($next->isToday() ? 'today' : $next->format('D, M j')) : 'Ended') : null"
                                        :edit-url="route('tasks.edit', $task)"
                                        :delete-url="route('tasks.destroy', $task)"
                                        confirm="Delete this task? Its completion history goes with it.">
                                        @if ($task->notes)
                                            <x-slot name="notes">{{ $task->notes }}</x-slot>
                                        @endif
                                    </x-timeline-item>
                                @endforeach
                            </ul>
                        </x-card>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('tasks.starter') }}" class="mt-8 text-center text-sm text-gray-500">
                @csrf
                Deleted a starter chore by mistake?
                <button type="submit" class="text-blue-600 hover:text-blue-700">Re-add missing starter chores</button>
            </form>
        @endif
    </div>
</x-app-layout>
