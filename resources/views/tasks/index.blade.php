<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tasks" subtitle="Your chores and how often they repeat." />
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if (session('status'))
            <div class="mb-4 flex items-center gap-3 rounded-xl bg-surface-highest px-4 py-3 text-body-md text-on-surface">
                <span class="icon text-[20px] text-tertiary">check</span>{{ session('status') }}
            </div>
        @endif

        @if (! $hasTasks)
            <x-card>
                <x-empty-state title="No tasks yet" subtitle="Add your own, or start with a list of common household chores already on a schedule." icon="checklist">
                    <x-slot name="action">
                        <form method="POST" action="{{ route('tasks.starter') }}">
                            @csrf
                            <x-primary-button><span class="icon text-[18px]">add</span> Add starter chores</x-primary-button>
                        </form>
                    </x-slot>
                </x-empty-state>
            </x-card>
        @else
            <div class="space-y-6">
                @foreach ($groups as $group)
                    <section>
                        <h2 class="mb-2 flex items-center gap-2 px-4 text-title-sm text-on-surface-variant">
                            <span class="h-2.5 w-2.5 rounded-full {{ $group['category']->dotClass() }}"></span>
                            {{ $group['category']->label() }}
                            <span class="text-outline">&middot; {{ $group['tasks']->count() }}</span>
                        </h2>
                        <x-card class="p-1.5">
                            <ul>
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
                    </section>
                @endforeach
            </div>

            <form method="POST" action="{{ route('tasks.starter') }}" class="mt-8 text-center text-body-md text-on-surface-variant">
                @csrf
                Deleted a starter chore by mistake?
                <button type="submit" class="ml-1 inline-flex h-10 items-center rounded-full px-3 text-label-lg text-primary hover:bg-primary/[0.08]">Re-add missing starter chores</button>
            </form>
        @endif
    </div>

    <x-fab icon="add" label="New task" :href="route('tasks.create')" />
</x-app-layout>
