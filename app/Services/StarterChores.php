<?php

namespace App\Services;

use App\Enums\RepeatFrequency;
use App\Models\ChoreTemplate;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class StarterChores
{
    /**
     * Copy every chore template into the user's task list, skipping any title
     * they already have — so it's safe to run again to restore deleted ones.
     *
     * @return int how many tasks were added
     */
    public static function addTo(User $user, ?CarbonInterface $today = null): int
    {
        $today = CarbonImmutable::instance($today ?? today())->startOfDay();
        $existing = $user->tasks()->pluck('title')->map(fn (string $title) => mb_strtolower($title));
        $added = 0;

        foreach (ChoreTemplate::query()->orderBy('sort_order')->get() as $template) {
            if ($existing->contains(mb_strtolower($template->title))) {
                continue;
            }

            $user->tasks()->create([
                'title' => $template->title,
                'category' => $template->category,
                'notes' => $template->notes,
                'starts_on' => self::startDate($template, $today),
                'due_time' => $template->due_time,
                'repeat' => $template->repeat,
                'repeat_interval' => $template->repeat_interval,
                'repeat_weekdays' => $template->repeat_weekdays,
            ]);

            $added++;
        }

        return $added;
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
