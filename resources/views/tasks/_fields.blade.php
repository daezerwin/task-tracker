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
     class="space-y-5">
    <div>
        <x-input-label for="title" value="Task" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required maxlength="255"
            placeholder="e.g. Clean the bathroom" value="{{ old('title', $task?->title) }}" />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="category" value="Category" />
        <select id="category" name="category" required
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-base py-2.5 px-3.5 focus:border-blue-600 focus:ring-blue-600">
            @foreach (\App\Enums\TaskCategory::cases() as $category)
                <option value="{{ $category->value }}" @selected(old('category', $task?->category->value ?? 'cleaning') === $category->value)>{{ $category->label() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category')" class="mt-1" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="starts_on" x-text="repeat === 'none' ? 'Date' : 'Starts on'" value="Date" />
            <x-text-input id="starts_on" name="starts_on" type="date" class="mt-1 block w-full" required
                value="{{ old('starts_on', $task?->starts_on?->toDateString() ?? $defaultDate->toDateString()) }}" />
            <x-input-error :messages="$errors->get('starts_on')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="due_time" value="Time (optional)" />
            <x-text-input id="due_time" name="due_time" type="time" class="mt-1 block w-full"
                value="{{ old('due_time', $task?->due_time ? substr($task->due_time, 0, 5) : null) }}" />
            <x-input-error :messages="$errors->get('due_time')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="repeat" value="Repeat" />
        <select id="repeat" name="repeat" x-model="repeat" required
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-base py-2.5 px-3.5 focus:border-blue-600 focus:ring-blue-600">
            @foreach (\App\Enums\RepeatFrequency::cases() as $frequency)
                <option value="{{ $frequency->value }}">{{ $frequency->label() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('repeat')" class="mt-1" />
    </div>

    <div x-show="repeat !== 'none'" x-cloak class="space-y-5 rounded-xl bg-blue-50/60 p-5">
        <div>
            <x-input-label for="repeat_interval" value="Every" />
            <div class="mt-1 flex items-center gap-3">
                <x-text-input id="repeat_interval" name="repeat_interval" type="number" min="1" max="52" class="w-24"
                    value="{{ old('repeat_interval', $task?->repeat_interval ?? 1) }}" />
                <span class="text-base text-gray-600" x-text="unit()"></span>
            </div>
            <x-input-error :messages="$errors->get('repeat_interval')" class="mt-1" />
        </div>

        <div x-show="repeat === 'weekly'">
            <x-input-label value="On these days" />
            <div class="mt-1 grid grid-cols-7 gap-1.5">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $index => $name)
                    <label class="flex items-center justify-center rounded-lg border-2 py-2.5 text-sm font-medium cursor-pointer transition"
                           :class="weekdays.includes('{{ $index }}') ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600'">
                        <input type="checkbox" name="repeat_weekdays[]" value="{{ $index }}" x-model="weekdays" class="sr-only">
                        {{ $name }}
                    </label>
                @endforeach
            </div>
            <p class="text-sm text-gray-500 mt-1">Leave all unticked to repeat on the start date's weekday.</p>
            <x-input-error :messages="$errors->get('repeat_weekdays')" class="mt-1" />
        </div>

        <p x-show="repeat === 'monthly'" class="text-sm text-gray-500">
            Repeats on the same day of the month as the start date (or the month's last day, if it's shorter).
        </p>

        <div>
            <x-input-label for="ends_on" value="Ends on (optional)" />
            <x-text-input id="ends_on" name="ends_on" type="date" class="mt-1 block w-full"
                value="{{ old('ends_on', $task?->ends_on?->toDateString()) }}" />
            <x-input-error :messages="$errors->get('ends_on')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="notes" value="Notes (optional)" />
        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm text-base py-2.5 px-3.5 focus:border-blue-600 focus:ring-blue-600">{{ old('notes', $task?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>
</div>
