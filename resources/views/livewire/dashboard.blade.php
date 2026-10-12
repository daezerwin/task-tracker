<?php

use App\Enums\RepeatFrequency;
use App\Enums\TaskCategory;
use App\Services\StarterChores;
use App\Services\TaskSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use function Livewire\Volt\{computed, layout, state};

layout('layouts.app');

// Changed only by the actions below, never bound to an input.
state(['dayOffset' => 0])->locked();

state([
    'quickTitle' => '',
    'quickCategory' => TaskCategory::Cleaning->value,
    'quickTime' => '',
    'quickRepeat' => RepeatFrequency::None->value,
]);

$schedule = computed(fn () => new TaskSchedule(Auth::user()));

$hasTasks = computed(fn () => Auth::user()->tasks()->exists());

$day = computed(fn () => today()->addDays($this->dayOffset));

$dayLabel = computed(fn () => match ($this->dayOffset) {
    0 => 'Today',
    -1 => 'Yesterday',
    1 => 'Tomorrow',
    default => $this->day->format('l'),
});

// Unfinished chores first; ticked ones sink to the bottom.
$items = computed(fn () => $this->schedule->on($this->day)->sortBy(fn ($item) => $item['done'])->values());

$overdue = computed(fn () => $this->dayOffset === 0 ? $this->schedule->overdue(today()) : collect());

$week = computed(function () {
    $start = today()->startOfWeek(TaskSchedule::WEEK_STARTS_ON);

    return $this->schedule->between($start, $start->copy()->addDays(6))
        ->map(fn ($items, $date) => [
            'date' => $date,
            'label' => TaskSchedule::parseDay($date)->format('D'),
            'done' => $items->where('done', true)->count(),
            'total' => $items->count(),
        ])
        ->values();
});

$prevDay = function () {
    $this->dayOffset--;
};

$nextDay = function () {
    $this->dayOffset++;
};

$goToday = function () {
    $this->dayOffset = 0;
};

$jumpTo = function (string $date) {
    if ($day = TaskSchedule::parseDay($date)) {
        $this->dayOffset = (int) round(today()->diffInDays($day));
    }
};

$toggle = function (int $taskId, string $date) {
    $this->schedule->toggle($taskId, $date);
};

$addStarterChores = function () {
    StarterChores::addTo(Auth::user());
};

$saveQuickTask = function () {
    $this->validate([
        'quickTitle' => ['required', 'string', 'max:255'],
        'quickCategory' => ['required', Rule::enum(TaskCategory::class)],
        'quickTime' => ['nullable', 'date_format:H:i'],
        'quickRepeat' => ['required', Rule::enum(RepeatFrequency::class)],
    ], attributes: [
        'quickTitle' => 'title',
        'quickCategory' => 'category',
        'quickTime' => 'time',
        'quickRepeat' => 'repeat',
    ]);

    $repeat = RepeatFrequency::from($this->quickRepeat);

    Auth::user()->tasks()->create([
        'title' => $this->quickTitle,
        'category' => $this->quickCategory,
        'starts_on' => $this->day,
        'due_time' => $this->quickTime ?: null,
        'repeat' => $repeat,
        'repeat_interval' => 1,
        'repeat_weekdays' => $repeat === RepeatFrequency::Weekly ? [$this->day->dayOfWeek] : null,
    ]);

    $this->reset('quickTitle', 'quickTime', 'quickRepeat');

    // Closed from the server only on this success path, so a validation
    // error keeps the modal open with the message showing.
    $this->dispatch('close-modal', 'quick-task');
};

?>

@php
    // Volt computed properties are only reachable via $this->, so alias them
    // to plain variables once for readability in the rest of this template.
    $day = $this->day;
    $dayLabel = $this->dayLabel;
    $items = $this->items;
    $overdue = $this->overdue;
    $doneCount = $items->where('done', true)->count();
    $total = $items->count();
@endphp

<div x-data>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        @if (! $this->hasTasks)
            <x-card class="p-10 text-center">
                <p class="text-gray-700 font-medium text-lg">Welcome! Let's set up your chores.</p>
                <p class="text-base text-gray-500 mt-1.5 max-w-xl mx-auto">
                    Start with a ready-made list of common household chores — cleaning, laundry, kitchen, bills and more,
                    each already on a sensible schedule — or add your own from scratch.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <x-primary-button type="button" wire:click="addStarterChores">Add starter chores</x-primary-button>
                    <a href="{{ route('tasks.create') }}" wire:navigate>
                        <x-secondary-button>+ Add my own</x-secondary-button>
                    </a>
                </div>
            </x-card>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <x-card class="p-6 flex flex-col justify-between gap-4">
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-gray-800 tabular-nums" x-text="$store.clock.now.toLocaleTimeString()"></p>
                        <p class="text-sm text-gray-400 mt-0.5" x-text="$store.clock.now.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' })"></p>
                    </div>
                    <a href="{{ route('calendar') }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-700">Open calendar →</a>
                </x-card>

                <x-card class="p-6 sm:col-span-2">
                    <div class="flex items-baseline justify-between gap-3">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ $dayLabel }}</p>
                        <p class="text-sm text-gray-500 tabular-nums">{{ $doneCount }} of {{ $total }} done</p>
                    </div>
                    <p class="text-2xl font-semibold text-gray-800 mt-1">
                        @if ($total === 0)
                            Nothing scheduled
                        @elseif ($doneCount === $total)
                            All done — nice work!
                        @else
                            {{ $total - $doneCount }} {{ Str::plural('chore', $total - $doneCount) }} left
                        @endif
                    </p>
                    <div class="mt-4 h-3 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" style="width: {{ $total ? $doneCount / $total * 100 : 0 }}%"></div>
                    </div>
                </x-card>
            </div>

            <div class="flex items-center justify-between gap-3">
                <button type="button" wire:click="prevDay" aria-label="Previous day"
                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 text-xl hover:bg-gray-50">
                    ‹
                </button>
                <div class="text-center">
                    <p class="font-semibold text-gray-800">{{ $day->format('l, F j') }}</p>
                    @if ($this->dayOffset !== 0)
                        <button type="button" wire:click="goToday" class="text-sm text-blue-600 hover:text-blue-700">Back to today</button>
                    @endif
                </div>
                <button type="button" wire:click="nextDay" aria-label="Next day"
                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 text-xl hover:bg-gray-50">
                    ›
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                <div class="lg:col-span-2 space-y-5">
                    @if ($overdue->isNotEmpty())
                        <x-card class="p-6">
                            <p class="font-semibold text-lg text-red-700">Overdue</p>
                            <p class="text-sm text-gray-500">One-off tasks from earlier days that haven't been ticked off yet.</p>
                            <ul class="divide-y divide-gray-100 mt-2">
                                @foreach ($overdue as $task)
                                    <x-task-row :task="$task" :date="$task->starts_on->toDateString()" overdue />
                                @endforeach
                            </ul>
                        </x-card>
                    @endif

                    <x-card class="p-6">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-lg text-gray-800">{{ $dayLabel }}'s Chores</p>
                            <a href="{{ route('tasks.index') }}" wire:navigate class="text-sm text-blue-600 hover:text-blue-700">Manage tasks →</a>
                        </div>

                        @if ($items->isEmpty())
                            <x-empty-state title="Nothing scheduled" subtitle="Enjoy the free time — or add something below." />
                        @else
                            <ul class="divide-y divide-gray-100 mt-2">
                                @foreach ($items as $item)
                                    <x-task-row :task="$item['task']" :done="$item['done']" :date="$day->toDateString()" />
                                @endforeach
                            </ul>
                        @endif
                    </x-card>

                    <button type="button" x-on:click="$dispatch('open-modal', 'quick-task')"
                            class="flex items-center justify-center w-full px-6 py-6 rounded-2xl bg-blue-600 text-white font-semibold text-lg shadow-sm hover:bg-blue-700 hover:shadow-md transition">
                        + Add a task for {{ $this->dayOffset === 0 ? 'today' : $day->format('D, M j') }}
                    </button>
                </div>

                <x-card class="p-6">
                    <p class="font-semibold text-lg text-gray-800 mb-1">This Week</p>
                    <p class="text-sm text-gray-400 mb-3">Chores done per day</p>
                    <div class="space-y-1">
                        @foreach ($this->week as $row)
                            <button type="button" wire:click="jumpTo('{{ $row['date'] }}')"
                                    class="w-full flex items-center gap-3 rounded-lg px-2 py-1.5 transition {{ $row['date'] === $day->toDateString() ? 'bg-blue-50' : 'hover:bg-gray-50' }}">
                                <span class="w-10 text-left text-sm font-medium shrink-0 {{ $row['date'] === today()->toDateString() ? 'text-blue-700' : 'text-gray-500' }}">{{ $row['label'] }}</span>
                                <span class="flex-1 h-3 rounded-full bg-gray-100 overflow-hidden">
                                    <span class="block h-full rounded-full bg-emerald-500" style="width: {{ $row['total'] ? $row['done'] / $row['total'] * 100 : 0 }}%"></span>
                                </span>
                                <span class="w-12 text-sm text-gray-600 text-right shrink-0 tabular-nums">{{ $row['done'] }}/{{ $row['total'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <!-- Quick Add Task Modal -->
            <x-modal name="quick-task" max-width="sm" focusable>
                <form wire:submit="saveQuickTask" class="p-8 space-y-5">
                    <h3 class="text-xl font-semibold text-gray-800">Add a task for {{ $this->dayOffset === 0 ? 'today' : $day->format('D, M j') }}</h3>

                    <div>
                        <x-input-label for="quickTitle" value="Task" />
                        <x-text-input id="quickTitle" wire:model="quickTitle" type="text" class="mt-1 block w-full" placeholder="e.g. Wash the car" />
                        <x-input-error :messages="$errors->get('quickTitle')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="quickCategory" value="Category" />
                        <select id="quickCategory" wire:model="quickCategory" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-base py-2.5 px-3.5 focus:border-blue-600 focus:ring-blue-600">
                            @foreach (\App\Enums\TaskCategory::cases() as $category)
                                <option value="{{ $category->value }}">{{ $category->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('quickCategory')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="quickTime" value="Time (optional)" />
                        <x-text-input id="quickTime" wire:model="quickTime" type="time" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('quickTime')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="quickRepeat" value="Repeat" />
                        <select id="quickRepeat" wire:model="quickRepeat" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-base py-2.5 px-3.5 focus:border-blue-600 focus:ring-blue-600">
                            <option value="none">Does not repeat</option>
                            <option value="daily">Every day</option>
                            <option value="weekly">Every {{ $day->format('l') }}</option>
                            <option value="monthly">Monthly on the {{ $day->ordinal('day') }}</option>
                        </select>
                        <p class="text-sm text-gray-400 mt-1">For other schedules, use the full task form.</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'quick-task')">Cancel</x-secondary-button>
                        <x-primary-button>Save Task</x-primary-button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</div>
