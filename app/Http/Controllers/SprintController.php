<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\SprintDailyProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SprintController extends Controller
{
    public function index(Request $request, Project $project): Response
    {
        $sprints = $project->sprints()
            ->withCount(['stories'])
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('Sprints/Index', [
            'project' => $project,
            'sprints' => $sprints,
        ]);
    }

    public function create(Project $project): Response
    {
        return Inertia::render('Sprints/Create', [
            'project' => $project,
            'suggestedNumber' => $project->getNextSprintNumber(),
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $sprint = $project->sprints()->create($validated);

        return redirect()->route('sprints.show', [$project, $sprint])
            ->with('success', 'Sprint created successfully.');
    }

    public function show(Request $request, Project $project, Sprint $sprint): Response
    {
        $sprint->load([
            'stories' => fn ($q) => $q->orderBy('order'),
            'stories.assignee',
            'stories.tasks',
            'stories.epic',
        ]);

        $columns = [
            ['id' => 'backlog', 'title' => 'Backlog', 'status' => 'backlog'],
            ['id' => 'todo', 'title' => 'To Do', 'status' => 'todo'],
            ['id' => 'in_progress', 'title' => 'In Progress', 'status' => 'in_progress'],
            ['id' => 'review', 'title' => 'Review', 'status' => 'review'],
            ['id' => 'done', 'title' => 'Done', 'status' => 'done'],
        ];

        $burndownData = $sprint->getBurndownData();

        return Inertia::render('Sprints/Show', [
            'project' => $project,
            'sprint' => $sprint,
            'columns' => $columns,
            'burndownData' => $burndownData,
            'isOnTrack' => $sprint->isOnTrack(),
        ]);
    }

    public function edit(Project $project, Sprint $sprint): Response
    {
        return Inertia::render('Sprints/Edit', [
            'project' => $project,
            'sprint' => $sprint,
        ]);
    }

    public function update(Request $request, Project $project, Sprint $sprint): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:planning,active,completed,cancelled',
        ]);

        // If activating sprint, check for existing active sprint
        if ($validated['status'] === 'active' && $sprint->status !== 'active') {
            $existingActive = $project->sprints()
                ->where('id', '!=', $sprint->id)
                ->where('status', 'active')
                ->exists();

            if ($existingActive) {
                return back()->with('error', 'There is already an active sprint. Complete or cancel it first.');
            }
        }

        $sprint->update($validated);

        // Record progress when sprint is activated
        if ($validated['status'] === 'active') {
            SprintDailyProgress::recordProgress($sprint);
        }

        return redirect()->route('sprints.show', [$project, $sprint])
            ->with('success', 'Sprint updated successfully.');
    }

    public function destroy(Project $project, Sprint $sprint): RedirectResponse
    {
        // Move stories back to backlog
        $sprint->stories()->update(['sprint_id' => null, 'status' => 'backlog']);

        $sprint->delete();

        return redirect()->route('sprints.index', $project)
            ->with('success', 'Sprint deleted successfully.');
    }

    /**
     * Sprint board (Kanban)
     */
    public function board(Request $request, Project $project, Sprint $sprint): Response
    {
        $sprint->load([
            'stories' => fn ($q) => $q->orderBy('order'),
            'stories.assignee',
            'stories.tasks',
            'stories.epic',
        ]);

        return Inertia::render('Sprints/Board', [
            'project' => $project,
            'sprint' => $sprint,
        ]);
    }

    /**
     * Sprint timeline view
     */
    public function timeline(Request $request, Project $project, Sprint $sprint): Response
    {
        $sprint->load([
            'stories' => fn ($q) => $q->orderBy('order'),
            'stories.assignee',
            'stories.tasks',
        ]);

        return Inertia::render('Sprints/Timeline', [
            'project' => $project,
            'sprint' => $sprint,
        ]);
    }

    /**
     * Sprint reports
     */
    public function reports(Request $request, Project $project, Sprint $sprint): Response
    {
        $sprint->load(['stories.tasks', 'dailyProgress']);

        $metrics = [
            'total_stories' => $sprint->stories->count(),
            'completed_stories' => $sprint->stories->where('status', 'done')->count(),
            'total_tasks' => $sprint->stories->flatMap->tasks->count(),
            'completed_tasks' => $sprint->stories->flatMap->tasks->where('status', 'done')->count(),
            'total_hours' => $sprint->total_hours,
            'completed_hours' => $sprint->completed_hours,
            'remaining_hours' => $sprint->remaining_hours,
            'progress_percentage' => $sprint->progress_percentage,
            'days_remaining' => $sprint->days_remaining,
            'is_on_track' => $sprint->isOnTrack(),
        ];

        return Inertia::render('Sprints/Reports', [
            'project' => $project,
            'sprint' => $sprint,
            'metrics' => $metrics,
            'burndownData' => $sprint->getBurndownData(),
        ]);
    }

    /**
     * Start sprint
     */
    public function start(Request $request, Project $project, Sprint $sprint): RedirectResponse
    {
        // Check for existing active sprint
        $existingActive = $project->sprints()
            ->where('id', '!=', $sprint->id)
            ->where('status', 'active')
            ->exists();

        if ($existingActive) {
            return back()->with('error', 'There is already an active sprint.');
        }

        $sprint->update(['status' => 'active']);
        SprintDailyProgress::recordProgress($sprint);

        return back()->with('success', 'Sprint started successfully.');
    }

    /**
     * Complete sprint
     */
    public function complete(Request $request, Project $project, Sprint $sprint): RedirectResponse
    {
        // Move incomplete stories back to backlog
        $sprint->stories()
            ->where('status', '!=', 'done')
            ->update(['sprint_id' => null, 'status' => 'backlog']);

        $sprint->update(['status' => 'completed']);

        return back()->with('success', 'Sprint completed. Incomplete stories moved to backlog.');
    }
}
