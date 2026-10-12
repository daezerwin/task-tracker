<?php

namespace Tests\Unit;

use App\Models\Task;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class TaskRecurrenceTest extends TestCase
{
    private function task(array $attributes): Task
    {
        return new Task($attributes + ['title' => 'Chore', 'repeat_interval' => 1, 'is_active' => true]);
    }

    private function day(string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date);
    }

    public function test_one_off_task_occurs_only_on_its_date(): void
    {
        $task = $this->task(['repeat' => 'none', 'starts_on' => '2026-10-14']);

        $this->assertTrue($task->occursOn($this->day('2026-10-14')));
        $this->assertFalse($task->occursOn($this->day('2026-10-13')));
        $this->assertFalse($task->occursOn($this->day('2026-10-15')));
    }

    public function test_daily_task_respects_its_interval(): void
    {
        $everyDay = $this->task(['repeat' => 'daily', 'starts_on' => '2026-10-14']);
        $everyThirdDay = $this->task(['repeat' => 'daily', 'repeat_interval' => 3, 'starts_on' => '2026-10-14']);

        $this->assertFalse($everyDay->occursOn($this->day('2026-10-13')));
        $this->assertTrue($everyDay->occursOn($this->day('2026-10-14')));
        $this->assertTrue($everyDay->occursOn($this->day('2027-03-01')));

        $this->assertTrue($everyThirdDay->occursOn($this->day('2026-10-14')));
        $this->assertFalse($everyThirdDay->occursOn($this->day('2026-10-15')));
        $this->assertFalse($everyThirdDay->occursOn($this->day('2026-10-16')));
        $this->assertTrue($everyThirdDay->occursOn($this->day('2026-10-17')));
    }

    public function test_weekly_task_occurs_on_its_weekdays(): void
    {
        // Wednesday and Saturday, starting Wednesday 14 Oct 2026.
        $task = $this->task(['repeat' => 'weekly', 'repeat_weekdays' => [3, 6], 'starts_on' => '2026-10-14']);

        $this->assertTrue($task->occursOn($this->day('2026-10-14')));  // Wed
        $this->assertFalse($task->occursOn($this->day('2026-10-15'))); // Thu
        $this->assertTrue($task->occursOn($this->day('2026-10-17')));  // Sat
        $this->assertTrue($task->occursOn($this->day('2026-10-21')));  // next Wed
        $this->assertFalse($task->occursOn($this->day('2026-10-10'))); // Sat before it started
    }

    public function test_every_other_week_counts_from_the_start_week(): void
    {
        $task = $this->task(['repeat' => 'weekly', 'repeat_interval' => 2, 'repeat_weekdays' => [6], 'starts_on' => '2026-10-14']);

        $this->assertTrue($task->occursOn($this->day('2026-10-17')));  // same week as the start
        $this->assertFalse($task->occursOn($this->day('2026-10-24')));
        $this->assertTrue($task->occursOn($this->day('2026-10-31')));
        $this->assertFalse($task->occursOn($this->day('2026-11-07')));
        $this->assertTrue($task->occursOn($this->day('2026-11-14')));
    }

    public function test_weekly_task_without_weekdays_uses_the_start_dates_weekday(): void
    {
        $task = $this->task(['repeat' => 'weekly', 'repeat_weekdays' => null, 'starts_on' => '2026-10-14']);

        $this->assertSame([3], $task->weekdays());
        $this->assertTrue($task->occursOn($this->day('2026-10-21')));
        $this->assertFalse($task->occursOn($this->day('2026-10-22')));
    }

    public function test_monthly_task_clamps_to_the_last_day_of_shorter_months(): void
    {
        $task = $this->task(['repeat' => 'monthly', 'starts_on' => '2027-01-31']);

        $this->assertTrue($task->occursOn($this->day('2027-01-31')));
        $this->assertTrue($task->occursOn($this->day('2027-02-28')));
        $this->assertFalse($task->occursOn($this->day('2027-03-28')));
        $this->assertTrue($task->occursOn($this->day('2027-03-31')));
        $this->assertTrue($task->occursOn($this->day('2027-04-30')));
    }

    public function test_monthly_task_respects_its_interval(): void
    {
        $quarterly = $this->task(['repeat' => 'monthly', 'repeat_interval' => 3, 'starts_on' => '2026-10-15']);

        $this->assertTrue($quarterly->occursOn($this->day('2026-10-15')));
        $this->assertFalse($quarterly->occursOn($this->day('2026-11-15')));
        $this->assertFalse($quarterly->occursOn($this->day('2026-12-15')));
        $this->assertTrue($quarterly->occursOn($this->day('2027-01-15')));
    }

    public function test_task_stops_after_its_end_date_and_when_paused(): void
    {
        $ending = $this->task(['repeat' => 'daily', 'starts_on' => '2026-10-14', 'ends_on' => '2026-10-16']);
        $paused = $this->task(['repeat' => 'daily', 'starts_on' => '2026-10-14', 'is_active' => false]);

        $this->assertTrue($ending->occursOn($this->day('2026-10-16')));
        $this->assertFalse($ending->occursOn($this->day('2026-10-17')));
        $this->assertFalse($paused->occursOn($this->day('2026-10-14')));
    }

    public function test_next_occurrence(): void
    {
        $weekly = $this->task(['repeat' => 'weekly', 'repeat_weekdays' => [6], 'starts_on' => '2026-10-01']);
        $future = $this->task(['repeat' => 'monthly', 'starts_on' => '2026-12-05']);
        $ended = $this->task(['repeat' => 'daily', 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-05']);
        $quarterly = $this->task(['repeat' => 'monthly', 'repeat_interval' => 3, 'starts_on' => '2026-10-01']);

        $this->assertSame('2026-10-17', $weekly->nextOccurrence($this->day('2026-10-14'))->toDateString());
        $this->assertSame('2026-12-05', $future->nextOccurrence($this->day('2026-10-14'))->toDateString());
        $this->assertNull($ended->nextOccurrence($this->day('2026-10-14')));
        $this->assertSame('2027-01-01', $quarterly->nextOccurrence($this->day('2026-10-14'))->toDateString());
    }

    public function test_schedule_labels(): void
    {
        $this->assertSame('Once on Wed, Oct 14', $this->task(['repeat' => 'none', 'starts_on' => '2026-10-14'])->scheduleLabel());
        $this->assertSame('Every day', $this->task(['repeat' => 'daily', 'starts_on' => '2026-10-14'])->scheduleLabel());
        $this->assertSame('Every 3 days', $this->task(['repeat' => 'daily', 'repeat_interval' => 3, 'starts_on' => '2026-10-14'])->scheduleLabel());
        $this->assertSame('Every Wed & Sat', $this->task(['repeat' => 'weekly', 'repeat_weekdays' => [6, 3], 'starts_on' => '2026-10-14'])->scheduleLabel());
        $this->assertSame('Every weekday', $this->task(['repeat' => 'weekly', 'repeat_weekdays' => [1, 2, 3, 4, 5], 'starts_on' => '2026-10-14'])->scheduleLabel());
        $this->assertSame('Every 2 weeks on Sat', $this->task(['repeat' => 'weekly', 'repeat_interval' => 2, 'repeat_weekdays' => [6], 'starts_on' => '2026-10-14'])->scheduleLabel());
        $this->assertSame('Monthly on the 1st', $this->task(['repeat' => 'monthly', 'starts_on' => '2026-11-01'])->scheduleLabel());
        $this->assertSame('Every 3 months on the 15th', $this->task(['repeat' => 'monthly', 'repeat_interval' => 3, 'starts_on' => '2026-10-15'])->scheduleLabel());
    }

    public function test_time_label(): void
    {
        $this->assertSame('8:00 PM', $this->task(['repeat' => 'none', 'starts_on' => '2026-10-14', 'due_time' => '20:00'])->timeLabel());
        $this->assertNull($this->task(['repeat' => 'none', 'starts_on' => '2026-10-14'])->timeLabel());
    }
}
