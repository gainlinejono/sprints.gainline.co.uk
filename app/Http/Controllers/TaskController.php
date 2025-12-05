<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Story;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(Request $request, Project $project, Story $story): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'required|numeric|min:0',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $validated['order'] = $story->tasks()->max('order') + 1;

        $story->tasks()->create($validated);

        return back()->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Project $project, Story $story, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_hours' => 'required|numeric|min:0',
            'status' => 'required|in:todo,in_progress,done',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        return back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Project $project, Story $story, Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Project $project, Story $story, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $task->update($validated);

        return back();
    }

    /**
     * Reorder tasks
     */
    public function reorder(Request $request, Project $project, Story $story): RedirectResponse
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['tasks'] as $taskData) {
                Task::where('id', $taskData['id'])->update([
                    'order' => $taskData['order'],
                ]);
            }
        });

        return back();
    }

    /**
     * Log time to task
     */
    public function logTime(Request $request, Project $project, Story $story, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string|max:500',
            'logged_at' => 'nullable|date|before_or_equal:today',
        ]);

        $task->logTime(
            $request->user(),
            $validated['hours'],
            $validated['description'] ?? null,
            $validated['logged_at'] ?? now()
        );

        return back()->with('success', 'Time logged successfully.');
    }
}
