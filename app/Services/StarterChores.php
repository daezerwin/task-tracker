<?php

namespace App\Services;

use App\Enums\RepeatFrequency;
use App\Models\ChoreTemplate;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class StarterChores
{
    /**
     * Copy chore templates into the user's task list, skipping any title they
     * already have — so it's safe to run again to restore deleted ones.
     *
     * @param  list<int>|null  $only  template ids to add, or null for all of them
     * @return int how many tasks were added
     */
    public static function addTo(User $user, ?CarbonInterface $today = null, ?array $only = null): int
    {
        $added = 0;

        foreach (self::missingFor($user) as $template) {
            if ($only !== null && ! in_array($template->id, $only, true)) {
                continue;
            }

            $user->tasks()->save(self::taskFrom($template, $today));

            $added++;
        }

        return $added;
    }

    /**
     * The templates whose title the user doesn't have as a task yet, in list order.
     *
     * @return Collection<int, ChoreTemplate>
     */
    public static function missingFor(User $user): Collection
    {
        $existing = $user->tasks()->pluck('title')->map(fn (string $title) => mb_strtolower($title));

        return ChoreTemplate::query()
            ->orderBy('sort_order')
            ->get()
            ->reject(fn (ChoreTemplate $template) => $existing->contains(mb_strtolower($template->title)))
            ->values();
    }

    /**
     * The unsaved task a template becomes — also handy for previewing its
     * schedule before the user decides to add it.
     */
    public static function taskFrom(ChoreTemplate $template, ?CarbonInterface $today = null): Task
    {
        $today = CarbonImmutable::instance($today ?? today())->startOfDay();

        return new Task([
            'title' => $template->title,
            'category' => $template->category,
            'notes' => $template->notes,
            'starts_on' => self::startDate($template, $today),
            'due_time' => $template->due_time,
            'repeat' => $template->repeat,
            'repeat_interval' => $template->repeat_interval,
            'repeat_weekdays' => $template->repeat_weekdays,
        ]);
    }

    /**
     * Monthly chores anchor on their template's day of the month — the next
     * one on or after today. Everything else simply starts today.
     */
    private static function startDate(ChoreTemplate $template, CarbonImmutable $today): CarbonImmutable
    {
        if ($template->repeat !== RepeatFrequency::Monthly || ! $template->month_day) {
            return $today;
        }

        $thisMonth = $today->day(min($template->month_day, $today->daysInMonth));

        if ($thisMonth->gte($today)) {
            return $thisMonth;
        }

        $nextMonth = $today->startOfMonth()->addMonth();

        return $nextMonth->day(min($template->month_day, $nextMonth->daysInMonth));
    }
}
