<?php

use App\Services\TaskSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

use function Livewire\Volt\{computed, layout, protect, state};

layout('layouts.app');

// Changed only by the actions below, never bound to an input.
state([
    'month' => fn () => today()->format('Y-m'),
    'selectedDate' => fn () => today()->toDateString(),
])->locked();

$schedule = computed(fn () => new TaskSchedule(Auth::user()));

$monthStart = computed(fn () => CarbonImmutable::createFromFormat('!Y-m', $this->month));

$days = computed(fn () => $this->schedule->between(
    $this->monthStart->startOfWeek(TaskSchedule::WEEK_STARTS_ON),
    $this->monthStart->endOfMonth()->endOfWeek(TaskSchedule::WEEK_ENDS_ON),
));

$cells = computed(fn () => $this->days->map(function ($items, $date) {
    $day = TaskSchedule::parseDay($date);

    return [
        'date' => $date,
        'number' => $day->day,
        'inMonth' => $day->format('Y-m') === $this->month,
        'isToday' => $day->isToday(),
        'items' => $items,
        'done' => $items->where('done', true)->count(),
    ];
})->values());

$selectedItems = computed(fn () => ($this->days->get($this->selectedDate)
    ?? $this->schedule->on(TaskSchedule::parseDay($this->selectedDate)))
    ->sortBy(fn ($item) => $item['done'])->values());

// Moving months keeps today selected when it's in view, otherwise the 1st.
$showMonth = protect(function (CarbonImmutable $month) {
    $this->month = $month->format('Y-m');
    $this->selectedDate = $month->isSameMonth(today()) ? today()->toDateString() : $month->toDateString();
});

$prevMonth = function () {
    $this->showMonth(CarbonImmutable::createFromFormat('!Y-m', $this->month)->subMonthNoOverflow());
};

$nextMonth = function () {
    $this->showMonth(CarbonImmutable::createFromFormat('!Y-m', $this->month)->addMonthNoOverflow());
};

$goToday = function () {
    $this->showMonth(CarbonImmutable::today()->startOfMonth());
};

$selectDay = function (string $date) {
    if ($day = TaskSchedule::parseDay($date)) {
        $this->selectedDate = $day->toDateString();
        $this->month = $day->format('Y-m');
    }
};

$toggle = function (int $taskId, string $date) {
    $this->schedule->toggle($taskId, $date);
};

?>

@php
    $selected = \App\Services\TaskSchedule::parseDay($this->selectedDate);
    $selectedItems = $this->selectedItems;
    $selectedDone = $selectedItems->where('done', true)->count();
@endphp

<div>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            <x-card class="p-3 sm:p-6 lg:col-span-2">
                <div class="flex items-center justify-between gap-3 mb-4 px-1">
                    <button type="button" wire:click="prevMonth" aria-label="Previous month"
                            class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 text-xl hover:bg-gray-50">
                        ‹
                    </button>
                    <div class="text-center">
                        <h2 class="font-semibold text-xl sm:text-2xl text-gray-800">{{ $this->monthStart->format('F Y') }}</h2>
                        @unless ($this->monthStart->isSameMonth(today()))
                            <button type="button" wire:click="goToday" class="text-sm text-blue-600 hover:text-blue-700">Back to this month</button>
                        @endunless
                    </div>
                    <button type="button" wire:click="nextMonth" aria-label="Next month"
                            class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 text-xl hover:bg-gray-50">
                        ›
                    </button>
                </div>

                <div class="grid grid-cols-7 text-center text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                        <div class="py-1">{{ $weekday }}</div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 gap-px bg-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                    @foreach ($this->cells as $cell)
                        <button type="button" wire:key="cell-{{ $cell['date'] }}" wire:click="selectDay('{{ $cell['date'] }}')"
                                class="min-h-16 sm:min-h-28 p-1 sm:p-1.5 flex flex-col items-start gap-1 text-left transition
                                       {{ $cell['date'] === $this->selectedDate ? 'bg-blue-50 ring-2 ring-inset ring-blue-500' : ($cell['inMonth'] ? 'bg-white hover:bg-blue-50/50' : 'bg-gray-50 hover:bg-gray-100') }}">
                            <span class="w-7 h-7 shrink-0 flex items-center justify-center rounded-full text-sm font-medium
                                         {{ $cell['isToday'] ? 'bg-blue-600 text-white' : ($cell['inMonth'] ? 'text-gray-700' : 'text-gray-300') }}">
                                {{ $cell['number'] }}
                            </span>

                            @if ($cell['items']->isNotEmpty())
                                {{-- Phones: a dot per chore, greyed once done --}}
                                <span class="flex flex-wrap gap-0.5 px-0.5 sm:hidden">
                                    @foreach ($cell['items']->take(6) as $item)
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item['done'] ? 'bg-gray-300' : $item['task']->category->dotClass() }}"></span>
                                    @endforeach
                                </span>

                                {{-- Wider screens: the first few chore titles --}}
                                <span class="hidden sm:flex flex-col gap-0.5 w-full min-w-0">
                                    @foreach ($cell['items']->take(3) as $item)
                                        <span class="flex items-center gap-1 text-xs min-w-0 {{ $item['done'] ? 'text-gray-400 line-through' : 'text-gray-700' }}">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $item['done'] ? 'bg-gray-300' : $item['task']->category->dotClass() }}"></span>
                                            <span class="truncate">{{ $item['task']->title }}</span>
                                        </span>
                                    @endforeach
                                    @if ($cell['items']->count() > 3)
                                        <span class="text-xs text-gray-400">+{{ $cell['items']->count() - 3 }} more</span>
                                    @endif
                                </span>

                                <span class="mt-auto px-0.5 text-[11px] tabular-nums {{ $cell['done'] === $cell['items']->count() ? 'text-emerald-600 font-semibold' : 'text-gray-400' }}">
                                    {{ $cell['done'] }}/{{ $cell['items']->count() }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </x-card>

            <x-card class="p-6">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                    {{ $selected->isToday() ? 'Today' : ($selected->isYesterday() ? 'Yesterday' : ($selected->isTomorrow() ? 'Tomorrow' : $selected->format('l'))) }}
                </p>
                <h3 class="text-xl font-semibold text-gray-800">{{ $selected->format('F j, Y') }}</h3>
                @if ($selectedItems->isNotEmpty())
                    <p class="text-sm text-gray-500 mt-1">{{ $selectedDone }} of {{ $selectedItems->count() }} done</p>
                @endif

                @if ($selectedItems->isEmpty())
                    <x-empty-state title="Nothing scheduled" subtitle="No chores fall on this day." />
                @else
                    <ul class="divide-y divide-gray-100 mt-2">
                        @foreach ($selectedItems as $item)
                            <x-task-row :task="$item['task']" :done="$item['done']" :date="$this->selectedDate" />
                        @endforeach
                    </ul>
                @endif

                <a href="{{ route('tasks.create', ['date' => $this->selectedDate]) }}" wire:navigate class="block mt-4">
                    <x-secondary-button class="w-full">+ Add task on this day</x-secondary-button>
                </a>
            </x-card>
        </div>
    </div>
</div>
