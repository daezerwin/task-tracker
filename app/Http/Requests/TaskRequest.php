<?php

namespace App\Http\Requests;

use App\Enums\RepeatFrequency;
use App\Enums\TaskCategory;
use App\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared by store and update — the rules are identical either way.
 */
class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task ? $this->user()->can('update', $task) : $this->user()->can('create', Task::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // The form posts a hidden 0 before the checkbox; anything that
            // omits the field entirely (e.g. the create form) means active.
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'repeat_interval' => $this->input('repeat_interval') ?: 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(TaskCategory::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'repeat' => ['required', Rule::enum(RepeatFrequency::class)],
            'repeat_interval' => ['required', 'integer', 'min:1', 'max:52'],
            'repeat_weekdays' => ['nullable', 'array'],
            'repeat_weekdays.*' => ['integer', 'between:0,6'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * The validated fields, with anything that doesn't apply to the chosen
     * repeat cleared — the form keeps hidden fields' old values around when
     * you switch, say, from Weekly back to Does not repeat.
     *
     * @return array<string, mixed>
     */
    public function taskData(): array
    {
        $data = $this->validated();
        $repeat = RepeatFrequency::from($data['repeat']);

        if ($repeat === RepeatFrequency::None) {
            $data['repeat_interval'] = 1;
            $data['ends_on'] = null;
        }

        $data['repeat_weekdays'] = $repeat === RepeatFrequency::Weekly
            ? collect($data['repeat_weekdays'] ?? [CarbonImmutable::parse($data['starts_on'])->dayOfWeek])
                ->map(fn ($day) => (int) $day)->unique()->sort()->values()->all()
            : null;

        return $data;
    }
}
