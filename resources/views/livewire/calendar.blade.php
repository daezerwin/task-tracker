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
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-8">
        <!-- Month header -->
        <div class="mb-4 flex items-center gap-1">
            <h1 class="min-w-0 flex-1 text-headline-sm sm:text-headline-lg text-on-surface">{{ $this->monthStart->format('F Y') }}</h1>
            @unless ($this->monthStart->isSameMonth(today()))
                <button type="button" wire:click="goToday"
                        class="mr-1 h-10 px-4 rounded-full border border-outline text-label-lg text-primary hover:bg-primary/[0.08]">
                    Today
                </button>
            @endunless
            <button type="button" wire:click="prevMonth" aria-label="Previous month"
                    class="flex h-12 w-12 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
                <span class="icon">chevron_left</span>
            </button>
            <button type="button" wire:click="nextMonth" aria-label="Next month"
                    class="flex h-12 w-12 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
                <span class="icon">chevron_right</span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
            <x-card class="p-2 sm:p-4 lg:col-span-2">
                <div class="grid grid-cols-7 pb-2 text-center text-label-md uppercase text-on-surface-variant">
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                        <div>{{ $weekday }}</div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 gap-px overflow-hidden rounded-2xl bg-outline-variant/60">
                    @foreach ($this->cells as $cell)
                        @php($isSelected = $cell['date'] === $this->selectedDate)
                        <button type="button" wire:key="cell-{{ $cell['date'] }}" wire:click="selectDay('{{ $cell['date'] }}')"
                                @if ($isSelected) aria-pressed="true" @endif
                                class="flex min-h-16 sm:min-h-28 flex-col items-center sm:items-start gap-1 p-1 sm:p-1.5 text-left transition
                                       {{ $isSelected ? 'bg-secondary-container/50' : ($cell['inMonth'] ? 'bg-surface-container hover:bg-surface-high' : 'bg-surface-low hover:bg-surface-container') }}">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-label-lg
                                         {{ $cell['isToday'] ? 'bg-primary text-on-primary' : ($isSelected ? 'bg-secondary-container text-on-secondary-container' : ($cell['inMonth'] ? 'text-on-surface' : 'text-outline')) }}">
                                {{ $cell['number'] }}
                            </span>

                            @if ($cell['items']->isNotEmpty())
                                {{-- Phones: a dot per chore, dimmed once done --}}
                                <span class="flex flex-wrap justify-center gap-0.5 px-0.5 sm:hidden">
                                    @foreach ($cell['items']->take(6) as $item)
                                        <span class="h-1.5 w-1.5 rounded-full {{ $item['done'] ? 'bg-outline-variant' : $item['task']->category->dotClass() }}"></span>
                                    @endforeach
                                </span>

                                {{-- Wider screens: the first few chore titles --}}
                                <span class="hidden w-full min-w-0 flex-col gap-0.5 sm:flex">
                                    @foreach ($cell['items']->take(3) as $item)
                                        <span class="flex min-w-0 items-center gap-1 text-label-sm {{ $item['done'] ? 'text-outline line-through' : 'text-on-surface-variant' }}">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $item['done'] ? 'bg-outline-variant' : $item['task']->category->dotClass() }}"></span>
                                            <span class="truncate">{{ $item['task']->title }}</span>
                                        </span>
                                    @endforeach
                                    @if ($cell['items']->count() > 3)
                                        <span class="text-label-sm text-outline">+{{ $cell['items']->count() - 3 }} more</span>
                                    @endif
                                </span>

                                <span class="mt-auto px-0.5 text-[11px] tabular-nums {{ $cell['done'] === $cell['items']->count() ? 'text-tertiary font-medium' : 'text-outline' }}">
                                    {{ $cell['done'] }}/{{ $cell['items']->count() }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </x-card>

            <x-card class="p-2 sm:p-3">
                <div class="px-3 pt-2">
                    <p class="text-label-lg text-primary">
                        {{ $selected->isToday() ? 'Today' : ($selected->isYesterday() ? 'Yesterday' : ($selected->isTomorrow() ? 'Tomorrow' : $selected->format('l'))) }}
                    </p>
                    <h2 class="text-title-lg text-on-surface">{{ $selected->format('F j, Y') }}</h2>
                    @if ($selectedItems->isNotEmpty())
                        <p class="mt-0.5 text-body-md text-on-surface-variant">{{ $selectedDone }} of {{ $selectedItems->count() }} done</p>
                    @endif
                </div>

                @if ($selectedItems->isEmpty())
                    <x-empty-state title="Nothing scheduled" subtitle="No chores fall on this day." icon="calendar_month" />
                @else
                    <ul class="mt-2">
                        @foreach ($selectedItems as $item)
                            <x-task-row :task="$item['task']" :done="$item['done']" :date="$this->selectedDate" />
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>
    </div>

    <x-fab icon="add" label="Add task" :href="route('tasks.create', ['date' => $this->selectedDate])" />
</div>
