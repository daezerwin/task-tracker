<?php

namespace App\Models;

use App\Enums\RepeatFrequency;
use App\Enums\TaskCategory;
use App\Services\TaskSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'category', 'notes', 'starts_on', 'due_time',
    'repeat', 'repeat_interval', 'repeat_weekdays', 'ends_on', 'is_active',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    private const WEEKDAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    protected $attributes = [
        'category' => 'other',
        'repeat' => 'none',
        'repeat_interval' => 1,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'category' => TaskCategory::class,
            'repeat' => RepeatFrequency::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'repeat_interval' => 'integer',
            'repeat_weekdays' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

    public function isRepeating(): bool
    {
        return $this->repeat !== RepeatFrequency::None;
    }

    /**
     * Whether this task is due on the given day. Weekly repeats count weeks
     * from the start of starts_on's week, and monthly repeats land on
     * starts_on's day of the month — clamped to the last day in shorter
     * months, so a task starting on the 31st still happens in February.
     */
    public function occursOn(CarbonInterface $date): bool
    {
        $day = CarbonImmutable::instance($date)->startOfDay();
        $start = CarbonImmutable::instance($this->starts_on)->startOfDay();

        if (! $this->is_active || $day->lt($start) || ($this->ends_on && $day->gt($this->ends_on))) {
            return false;
        }

        $interval = max(1, $this->repeat_interval);

        return match ($this->repeat) {
            RepeatFrequency::None => $day->equalTo($start),
            RepeatFrequency::Daily => (int) round($start->diffInDays($day)) % $interval === 0,
            RepeatFrequency::Weekly => in_array($day->dayOfWeek, $this->weekdays(), true)
                && (int) round($start->startOfWeek(TaskSchedule::WEEK_STARTS_ON)->diffInWeeks($day->startOfWeek(TaskSchedule::WEEK_STARTS_ON))) % $interval === 0,
            RepeatFrequency::Monthly => $day->day === min($start->day, $day->daysInMonth)
                && (($day->year - $start->year) * 12 + $day->month - $start->month) % $interval === 0,
        };
    }

    public function nextOccurrence(?CarbonInterface $from = null): ?CarbonImmutable
    {
        $day = CarbonImmutable::instance($from ?? today())->startOfDay();
        $interval = max(1, $this->repeat_interval);

        // Far enough ahead to always span one full repeat cycle.
        $horizon = match ($this->repeat) {
            RepeatFrequency::None => 0,
            RepeatFrequency::Daily => $interval,
            RepeatFrequency::Weekly => 7 * $interval + 7,
            RepeatFrequency::Monthly => 31 * $interval + 31,
        };

        if ($day->lt($this->starts_on)) {
            $day = CarbonImmutable::instance($this->starts_on)->startOfDay();
        }

        for ($i = 0; $i <= $horizon; $i++) {
            $candidate = $day->addDays($i);

            if ($this->occursOn($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /** @return list<int> 0 (Sunday) through 6 (Saturday) */
    public function weekdays(): array
    {
        return array_map('intval', $this->repeat_weekdays ?: [$this->starts_on->dayOfWeek]);
    }

    public function scheduleLabel(): string
    {
        $interval = max(1, $this->repeat_interval);

        return match ($this->repeat) {
            RepeatFrequency::None => 'Once on '.$this->starts_on->format('D, M j'),
            RepeatFrequency::Daily => $interval === 1 ? 'Every day' : "Every {$interval} days",
            RepeatFrequency::Weekly => $this->weeklyLabel($interval),
            RepeatFrequency::Monthly => ($interval === 1 ? 'Monthly' : "Every {$interval} months")
                .' on the '.$this->starts_on->ordinal('day'),
        };
    }

    public function timeLabel(): ?string
    {
        return $this->due_time
            ? CarbonImmutable::createFromFormat('H:i', substr($this->due_time, 0, 5))->format('g:i A')
            : null;
    }

    private function weeklyLabel(int $interval): string
    {
        $weekdays = $this->weekdays();
        sort($weekdays);

        if ($interval === 1 && count($weekdays) === 7) {
            return 'Every day';
        }

        if ($interval === 1 && $weekdays === [1, 2, 3, 4, 5]) {
            return 'Every weekday';
        }

        $days = collect($weekdays)->map(fn (int $day) => self::WEEKDAY_NAMES[$day])->join(', ', ' & ');

        return $interval === 1 ? "Every {$days}" : "Every {$interval} weeks on {$days}";
    }
}
