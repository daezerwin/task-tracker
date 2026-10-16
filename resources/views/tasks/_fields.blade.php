@php
    $task = $task ?? null;
    $defaultDate = $defaultDate ?? today();
    $weekdays = array_map('strval', old('repeat_weekdays', $task?->repeat_weekdays ?? []));
@endphp

<div x-data="{
        repeat: @js(old('repeat', $task?->repeat->value ?? 'none')),
        weekdays: @js($weekdays),
        unit() { return { daily: 'days', weekly: 'weeks', monthly: 'months' }[this.repeat] ?? '' },
     }"
     class="space-y-6">
    <div>
        <x-input-label for="title" value="Task" />
        <x-text-input id="title" name="title" type="text" required maxlength="255"
            placeholder="e.g. Clean the bathroom" value="{{ old('title', $task?->title) }}" />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="category" value="Category" />
        <select id="category" name="category" required class="m3-field">
            @foreach (\App\Enums\TaskCategory::cases() as $category)
                <option value="{{ $category->value }}" @selected(old('category', $task?->category->value ?? 'cleaning') === $category->value)>{{ $category->label() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="starts_on" x-text="repeat === 'none' ? 'Date' : 'Starts on'" value="Date" />
            <x-text-input id="starts_on" name="starts_on" type="date" required
                value="{{ old('starts_on', $task?->starts_on?->toDateString() ?? $defaultDate->toDateString()) }}" />
            <x-input-error :messages="$errors->get('starts_on')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="due_time" value="Time (optional)" />
            <x-text-input id="due_time" name="due_time" type="time"
                value="{{ old('due_time', $task?->due_time ? substr($task->due_time, 0, 5) : null) }}" />
            <x-input-error :messages="$errors->get('due_time')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="repeat" value="Repeat" />
        <select id="repeat" name="repeat" x-model="repeat" required class="m3-field">
            @foreach (\App\Enums\RepeatFrequency::cases() as $frequency)
                <option value="{{ $frequency->value }}">{{ $frequency->label() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('repeat')" class="mt-1" />
    </div>

    <div x-show="repeat !== 'none'" x-cloak class="space-y-6 rounded-2xl bg-surface-high p-5">
        <div>
            <x-input-label for="repeat_interval" value="Every" />
            <div class="flex items-center gap-3">
                <x-text-input id="repeat_interval" name="repeat_interval" type="number" min="1" max="52" class="!w-24"
                    value="{{ old('repeat_interval', $task?->repeat_interval ?? 1) }}" />
                <span class="text-body-lg text-on-surface-variant" x-text="unit()"></span>
            </div>
            <x-input-error :messages="$errors->get('repeat_interval')" class="mt-1" />
        </div>

        <div x-show="repeat === 'weekly'">
            <x-input-label value="On these days" />
            {{-- Material filter chips --}}
            <div class="flex flex-wrap gap-2">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $index => $name)
                    <label class="inline-flex h-8 cursor-pointer items-center gap-1 rounded-lg border px-3 text-label-lg transition"
                           :class="weekdays.includes('{{ $index }}') ? 'border-transparent bg-secondary-container text-on-secondary-container' : 'border-outline text-on-surface-variant hover:bg-on-surface/[0.08]'">
                        <input type="checkbox" name="repeat_weekdays[]" value="{{ $index }}" x-model="weekdays" class="sr-only">
                        <span class="icon -ml-1 text-[18px]" x-show="weekdays.includes('{{ $index }}')">check</span>
                        {{ $name }}
                    </label>
                @endforeach
            </div>
            <p class="mt-2 text-body-sm text-on-surface-variant">Leave all unselected to repeat on the start date's weekday.</p>
            <x-input-error :messages="$errors->get('repeat_weekdays')" class="mt-1" />
        </div>

        <p x-show="repeat === 'monthly'" class="text-body-md text-on-surface-variant">
            Repeats on the same day of the month as the start date (or the month's last day, if it's shorter).
        </p>

        <div>
            <x-input-label for="ends_on" value="Ends on (optional)" />
            <x-text-input id="ends_on" name="ends_on" type="date"
                value="{{ old('ends_on', $task?->ends_on?->toDateString()) }}" />
            <x-input-error :messages="$errors->get('ends_on')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notes (optional)" />
        <textarea id="notes" name="notes" rows="3" class="m3-field">{{ old('notes', $task?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>
</div>
