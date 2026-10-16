@props(['task', 'date', 'done' => false, 'overdue' => false])

{{-- A tickable task. Must sit inside a Livewire component with a toggle(int $taskId, string $date) action. --}}
<li wire:key="task-{{ $task->id }}-{{ $date }}" class="group/row flex items-center gap-1 rounded-2xl pr-1 hover:bg-on-surface/[0.04]">
    <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 py-2.5 pl-1">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full group-hover/row:bg-on-surface/[0.08]">
            <input type="checkbox" @checked($done)
                   wire:click="toggle({{ $task->id }}, '{{ $date }}')"
                   class="m3-checkbox">
        </span>
        <span class="min-w-0">
            <span class="block truncate text-body-lg {{ $done ? 'text-outline line-through' : 'text-on-surface' }}">{{ $task->title }}</span>
            <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-body-md text-on-surface-variant">
                <x-category-badge :category="$task->category" />
                @if ($task->timeLabel())
                    <span class="inline-flex items-center gap-1 tabular-nums"><span class="icon text-[16px]">schedule</span>{{ $task->timeLabel() }}</span>
                @endif
                @if ($overdue)
                    <span class="inline-flex items-center gap-1 text-error"><span class="icon text-[16px]">warning</span>Was due {{ $task->starts_on->format('D, M j') }}</span>
                @elseif ($task->isRepeating())
                    <span class="inline-flex items-center gap-1"><span class="icon text-[16px]">repeat</span>{{ $task->scheduleLabel() }}</span>
                @endif
            </span>
        </span>
    </label>
    <a href="{{ route('tasks.edit', $task) }}" wire:navigate aria-label="Edit {{ $task->title }}" title="Edit"
       class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
        <span class="icon text-[20px]">edit</span>
    </a>
</li>
