<?php

namespace App\Services;

use App\Enums\RepeatFrequency;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Expands a user's tasks into the days they fall on, paired with whether each
 * occurrence has been ticked off. Shared by the dashboard and the calendar.
 */
class TaskSchedule
{
    public const WEEK_STARTS_ON = CarbonInterface::SUNDAY;

    public const WEEK_ENDS_ON = CarbonInterface::SATURDAY;

    public function __construct(private User $user) {}

    /**
     * @return Collection<string, Collection<int, array{task: Task, done: bool}>> keyed by Y-m-d
     */
    public function between(CarbonInterface $start, CarbonInterface $end): Collection
    {
        $tasks = $this->user->tasks()->where('is_active', true)->get()
            ->sortBy(fn (Task $task) => [$task->due_time ?? '99:99', $task->title])
            ->values();

        $done = TaskCompletion::query()
            ->whereIn('task_id', $tasks->modelKeys())
            ->whereBetween('due_on', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->mapWithKeys(fn (TaskCompletion $completion) => [$completion->task_id.'|'.$completion->due_on => true]);

        return collect(CarbonPeriod::create($start->toDateString(), $end->toDateString()))
            ->mapWithKeys(fn (CarbonInterface $day) => [
                $day->toDateString() => $tasks
                    ->filter(fn (Task $task) => $task->occursOn($day))
                    ->map(fn (Task $task) => [
                        'task' => $task,
                        'done' => $done->has($task->id.'|'.$day->toDateString()),
                    ])
                    ->values(),
            ]);
    }

    /**
     * @return Collection<int, array{task: Task, done: bool}>
     */
    public function on(CarbonInterface $day): Collection
    {
        return $this->between($day, $day)->first();
    }

    /**
     * One-off tasks from before today that were never ticked off. Missed
     * repeats aren't carried over — the chore simply comes round again.
     *
     * @return Collection<int, Task>
     */
    public function overdue(CarbonInterface $today): Collection
    {
        return $this->user->tasks()
            ->where('is_active', true)
            ->where('repeat', RepeatFrequency::None)
            ->whereDate('starts_on', '<', $today->toDateString())
            ->whereDoesntHave('completions')
            ->orderBy('starts_on')
            ->get();
    }

    /**
     * Tick or untick one occurrence of one of this user's tasks. 404s for a
     * task that isn't theirs, or a day the task doesn't fall on.
     *
     * @return bool whether the occurrence is now done
     */
    public function toggle(int $taskId, string $date): bool
    {
        $task = $this->user->tasks()->find($taskId);
        $day = self::parseDay($date);

        abort_unless($task && $day && $task->occursOn($day), 404);

        $existing = $task->completions()->where('due_on', $day->toDateString())->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        $task->completions()->create(['due_on' => $day->toDateString(), 'completed_at' => now()]);

        return true;
    }

    /**
     * Parse a strict Y-m-d string, returning null (rather than throwing) for
     * anything else — these come straight from the browser.
     */
    public static function parseDay(?string $date): ?CarbonImmutable
    {
        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $day = CarbonImmutable::createFromFormat('!Y-m-d', $date);

        return $day && $day->toDateString() === $date ? $day : null;
    }
}
