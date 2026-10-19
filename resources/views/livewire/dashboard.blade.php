<?php

use App\Enums\RepeatFrequency;
use App\Enums\TaskCategory;
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

// Rolling seven days from today for the agenda slider, whichever day is being viewed.
$agenda = computed(fn () => $this->schedule->between(today(), today()->addDays(6))
    ->map(fn ($items, $date) => [
        'date' => $date,
        'day' => TaskSchedule::parseDay($date),
        'items' => $items,
        'done' => $items->where('done', true)->count(),
    ])
    ->values());

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
    $forDay = $this->dayOffset === 0 ? 'today' : $day->format('D, M j');
@endphp

<div x-data>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-8 space-y-4">

        @if (! $this->hasTasks)
            <x-card class="px-6 py-12 sm:px-10 text-center">
                <span class="icon icon-filled text-[48px] text-primary">checklist</span>
                <p class="mt-4 text-headline-sm text-on-surface">Welcome! Let's set up your chores.</p>
                <p class="mt-2 text-body-lg text-on-surface-variant max-w-xl mx-auto">
                    Start with a ready-made list of common household chores — cleaning, laundry, kitchen, bills and more,
                    each already on a sensible schedule — or add your own from scratch.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <x-primary-button type="button" x-on:click="$dispatch('open-modal', 'starter-chores')">
                        <span class="icon text-[18px]">add</span> Add starter chores
                    </x-primary-button>
                    <a href="{{ route('tasks.create') }}" wire:navigate>
                        <x-secondary-button>Add my own</x-secondary-button>
                    </a>
                </div>
            </x-card>

            <x-starter-chores-wizard />
        @else
            <!-- Day header -->
            <div class="flex items-center gap-1">
                <div class="min-w-0 flex-1">
                    <p class="text-label-lg text-primary">{{ $dayLabel }}</p>
                    <h1 class="text-headline-sm sm:text-headline-lg text-on-surface">{{ $day->format('l, F j') }}</h1>
                </div>
                @if ($this->dayOffset !== 0)
                    <button type="button" wire:click="goToday"
                            class="mr-1 h-10 px-4 rounded-full border border-outline text-label-lg text-primary hover:bg-primary/[0.08]">
                        Today
                    </button>
                @endif
                <button type="button" wire:click="prevDay" aria-label="Previous day"
                        class="flex h-12 w-12 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
                    <span class="icon">chevron_left</span>
                </button>
                <button type="button" wire:click="nextDay" aria-label="Next day"
                        class="flex h-12 w-12 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08]">
                    <span class="icon">chevron_right</span>
                </button>
            </div>

            <!-- Progress -->
            <x-card class="p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-title-lg text-on-surface">
                            @if ($total === 0)
                                Nothing scheduled
                            @elseif ($doneCount === $total)
                                All done — nice work!
                            @else
                                {{ $total - $doneCount }} {{ Str::plural('chore', $total - $doneCount) }} left
                            @endif
                        </p>
                        <p class="mt-0.5 text-body-md text-on-surface-variant">{{ $doneCount }} of {{ $total }} done</p>
                    </div>
                    <p class="text-title-md text-on-surface-variant tabular-nums"
                       x-text="$store.clock.now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })"></p>
                </div>
                <div class="mt-4 h-1.5 rounded-full bg-surface-highest overflow-hidden">
                    <div class="h-full rounded-full bg-primary transition-all duration-300" style="width: {{ $total ? $doneCount / $total * 100 : 0 }}%"></div>
                </div>
            </x-card>

            <!-- Next 7 days: swipe on touch, arrows on wider screens; tapping a card opens that day below -->
            <section x-data="{
                         atStart: true,
                         atEnd: false,
                         update() {
                             const track = this.$refs.track;
                             this.atStart = track.scrollLeft <= 4;
                             this.atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
                         },
                         slide(direction) {
                             this.$refs.track.scrollBy({ left: direction * this.$refs.track.clientWidth * 0.8, behavior: 'smooth' });
                         },
                     }"
                     x-init="update()" x-on:resize.window="update()">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-title-md text-on-surface">Next 7 days</p>
                    <div class="hidden sm:flex">
                        <button type="button" x-on:click="slide(-1)" x-bind:disabled="atStart" aria-label="Scroll back"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08] disabled:opacity-40 disabled:hover:bg-transparent">
                            <span class="icon">chevron_left</span>
                        </button>
                        <button type="button" x-on:click="slide(1)" x-bind:disabled="atEnd" aria-label="Scroll forward"
                                class="flex h-10 w-10 items-center justify-center rounded-full text-on-surface-variant hover:bg-on-surface/[0.08] disabled:opacity-40 disabled:hover:bg-transparent">
                            <span class="icon">chevron_right</span>
                        </button>
                    </div>
                </div>

                {{-- Bleeds to the screen edge below lg so the next card peeks in as a swipe hint. --}}
                <div x-ref="track" x-on:scroll.passive="update()"
                     class="scrollbar-none -mx-4 mt-2 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 sm:-mx-6 sm:scroll-px-6 sm:px-6 lg:mx-0 lg:scroll-px-0 lg:px-0">
                    @foreach ($this->agenda as $card)
                        @php
                            $cardTotal = $card['items']->count();
                            $isSelected = $card['date'] === $day->toDateString();
                        @endphp
                        <button type="button" wire:key="agenda-{{ $card['date'] }}" wire:click="jumpTo('{{ $card['date'] }}')"
                                aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                class="flex w-[80%] shrink-0 snap-start flex-col rounded-3xl bg-surface-container p-4 text-left transition hover:bg-surface-high focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary sm:w-64 lg:w-72 {{ $isSelected ? 'ring-2 ring-inset ring-primary' : '' }}">
                            <span class="flex w-full items-start justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block text-label-lg {{ $loop->first ? 'text-primary' : 'text-on-surface-variant' }}">
                                        {{ match ($loop->index) { 0 => 'Today', 1 => 'Tomorrow', default => $card['day']->format('l') } }}
                                    </span>
                                    <span class="block text-title-lg text-on-surface">{{ $card['day']->format('M j') }}</span>
                                </span>
                                <span class="shrink-0 text-body-md tabular-nums text-on-surface-variant">{{ $card['done'] }}/{{ $cardTotal }}</span>
                            </span>
                            <span class="mt-3 block h-1 w-full overflow-hidden rounded-full bg-surface-highest">
                                <span class="block h-full rounded-full bg-primary" style="width: {{ $cardTotal ? $card['done'] / $cardTotal * 100 : 0 }}%"></span>
                            </span>

                            @if ($cardTotal === 0)
                                <span class="mt-4 block text-body-md text-on-surface-variant">Nothing scheduled</span>
                            @else
                                <span class="mt-3 block w-full space-y-2">
                                    @foreach ($card['items']->take(5) as $item)
                                        <span class="flex items-center gap-2 text-body-md">
                                            @if ($item['done'])
                                                <span class="icon text-[16px] text-primary">check</span>
                                            @else
                                                <span class="mx-1 h-2 w-2 shrink-0 rounded-full {{ $item['task']->category->dotClass() }}"></span>
                                            @endif
                                            <span class="min-w-0 flex-1 truncate {{ $item['done'] ? 'text-outline line-through' : 'text-on-surface' }}">{{ $item['task']->title }}</span>
                                            @if ($item['task']->timeLabel())
                                                <span class="shrink-0 tabular-nums text-on-surface-variant">{{ $item['task']->timeLabel() }}</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </span>
                                @if ($cardTotal > 5)
                                    <span class="mt-2 block text-label-md text-primary">+{{ $cardTotal - 5 }} more</span>
                                @endif
                            @endif
                        </button>
                    @endforeach
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                <div class="lg:col-span-2 space-y-4">
                    @if ($overdue->isNotEmpty())
                        <x-card class="p-2 sm:p-3">
                            <div class="flex items-center gap-2 px-3 pt-2">
                                <span class="icon text-[20px] text-error">warning</span>
                                <p class="text-title-md text-error">Overdue</p>
                            </div>
                            <p class="px-3 pb-1 text-body-md text-on-surface-variant">One-off tasks from earlier days that haven't been ticked off yet.</p>
                            <ul>
                                @foreach ($overdue as $task)
                                    <x-task-row :task="$task" :date="$task->starts_on->toDateString()" overdue />
                                @endforeach
                            </ul>
                        </x-card>
                    @endif

                    <x-card class="p-2 sm:p-3">
                        <div class="flex items-center justify-between gap-3 pl-3">
                            <p class="text-title-md text-on-surface">{{ $dayLabel }}'s chores</p>
                            <a href="{{ route('tasks.index') }}" wire:navigate
                               class="inline-flex h-10 items-center rounded-full px-3 text-label-lg text-primary hover:bg-primary/[0.08]">
                                Manage
                            </a>
                        </div>

                        @if ($items->isEmpty())
                            <x-empty-state title="Nothing scheduled" subtitle="Enjoy the free time — or add something with the button below." />
                        @else
                            <ul>
                                @foreach ($items as $item)
                                    <x-task-row :task="$item['task']" :done="$item['done']" :date="$day->toDateString()" />
                                @endforeach
                            </ul>
                        @endif
                    </x-card>
                </div>

                <x-card class="p-5 sm:p-6">
                    <p class="text-title-md text-on-surface">This week</p>
                    <p class="mb-3 text-body-md text-on-surface-variant">Chores done per day</p>
                    <div class="space-y-1">
                        @foreach ($this->week as $row)
                            <button type="button" wire:click="jumpTo('{{ $row['date'] }}')"
                                    class="flex w-full items-center gap-3 rounded-full px-3 py-1.5 transition {{ $row['date'] === $day->toDateString() ? 'bg-secondary-container text-on-secondary-container' : 'hover:bg-on-surface/[0.08]' }}">
                                <span class="w-9 shrink-0 text-left text-label-lg {{ $row['date'] === today()->toDateString() ? 'text-primary' : ($row['date'] === $day->toDateString() ? '' : 'text-on-surface-variant') }}">{{ $row['label'] }}</span>
                                <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-highest">
                                    <span class="block h-full rounded-full bg-primary" style="width: {{ $row['total'] ? $row['done'] / $row['total'] * 100 : 0 }}%"></span>
                                </span>
                                <span class="w-11 shrink-0 text-right text-body-md tabular-nums">{{ $row['done'] }}/{{ $row['total'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <x-fab icon="add" label="Add task" x-on:click="$dispatch('open-modal', 'quick-task')" />

            <!-- Quick add dialog -->
            <x-modal name="quick-task" max-width="md" focusable>
                <form wire:submit="saveQuickTask" class="p-6 space-y-5">
                    <h2 class="text-headline-sm text-on-surface">New task for {{ $forDay }}</h2>

                    <div>
                        <x-input-label for="quickTitle" value="Task" />
                        <x-text-input id="quickTitle" wire:model="quickTitle" type="text" placeholder="e.g. Wash the car" />
                        <x-input-error :messages="$errors->get('quickTitle')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="quickCategory" value="Category" />
                            <select id="quickCategory" wire:model="quickCategory" class="m3-field">
                                @foreach (\App\Enums\TaskCategory::cases() as $category)
                                    <option value="{{ $category->value }}">{{ $category->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('quickCategory')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="quickTime" value="Time (optional)" />
                            <x-text-input id="quickTime" wire:model="quickTime" type="time" />
                            <x-input-error :messages="$errors->get('quickTime')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="quickRepeat" value="Repeat" />
                        <select id="quickRepeat" wire:model="quickRepeat" class="m3-field">
                            <option value="none">Does not repeat</option>
                            <option value="daily">Every day</option>
                            <option value="weekly">Every {{ $day->format('l') }}</option>
                            <option value="monthly">Monthly on the {{ $day->ordinal('day') }}</option>
                        </select>
                        <p class="mt-1 text-body-sm text-on-surface-variant">For other schedules, use the full task form.</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" x-on:click="$dispatch('close-modal', 'quick-task')"
                                class="h-10 px-4 rounded-full text-label-lg text-primary hover:bg-primary/[0.08]">Cancel</button>
                        <button type="submit"
                                class="h-10 px-4 rounded-full text-label-lg text-primary hover:bg-primary/[0.08]">Save</button>
                    </div>
                </form>
            </x-modal>
        @endif
    </div>
</div>
