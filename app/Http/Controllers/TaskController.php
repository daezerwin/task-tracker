<?php

namespace App\Http\Controllers;

use App\Enums\TaskCategory;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Services\TaskSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $tasks = Auth::user()->tasks()->orderBy('title')->get();

        $groups = collect(TaskCategory::cases())
            ->map(fn (TaskCategory $category) => [
                'category' => $category,
                'tasks' => $tasks->filter(fn (Task $task) => $task->category === $category)->values(),
            ])
            ->filter(fn (array $group) => $group['tasks']->isNotEmpty())
            ->values();

        return view('tasks.index', [
            'groups' => $groups,
            'hasTasks' => $tasks->isNotEmpty(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'date' => TaskSchedule::parseDay($request->query('date')) ?? today(),
        ]);
    }

    public function store(TaskRequest $request): RedirectResponse
    {
        Auth::user()->tasks()->create($request->taskData());

        return redirect()->route('tasks.index')->with('status', 'Task added.');
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', ['task' => $task]);
    }

    public function update(TaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->taskData());

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Task deleted.');
    }
}
