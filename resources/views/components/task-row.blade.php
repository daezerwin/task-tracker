@props(['task', 'date', 'done' => false, 'overdue' => false])

{{-- A tickable task. Must sit inside a Livewire component with a toggle(int $taskId, string $date) action. --}}
<li wire:key="task-{{ $task->id }}-{{ $date }}" class="flex items-center gap-4 py-3.5">
    <label class="flex items-center gap-4 min-w-0 flex-1 cursor-pointer">
        <input type="checkbox" @checked($done)
               wire:click="toggle({{ $task->id }}, '{{ $date }}')"
               class="w-7 h-7 shrink-0 rounded-lg border-2 border-gray-300 text-emerald-500 cursor-pointer focus:ring-emerald-500 focus:ring-offset-0">
        <span class="min-w-0">
            <span class="block text-base font-medium truncate {{ $done ? 'text-gray-400 line-through' : 'text-gray-800' }}">{{ $task->title }}</span>
            <span class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-gray-500">
                <x-category-badge :category="$task->category" />
                @if ($task->timeLabel())
                    <span class="tabular-nums">{{ $task->timeLabel() }}</span>
                @endif
                @if ($overdue)
                    <span class="text-red-600 font-medium">Was due {{ $task->starts_on->format('D, M j') }}</span>
                @elseif ($task->isRepeating())
                    <span class="text-gray-400">{{ $task->scheduleLabel() }}</span>
                @endif
            </span>
        </span>
    </label>
    <a href="{{ route('tasks.edit', $task) }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-700 shrink-0">Edit</a>
</li>
