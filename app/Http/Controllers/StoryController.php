<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StoryController extends Controller
{
    public function index(Request $request, Project $project): Response
    {
        $stories = $project->stories()
            ->with(['assignee', 'reporter', 'epic', 'sprint'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->epic_id, fn ($q, $epicId) => $q->where('epic_id', $epicId))
            ->when($request->sprint_id, fn ($q, $sprintId) => $q->where('sprint_id', $sprintId))
            ->when($request->assignee_id, fn ($q, $assigneeId) => $q->where('assignee_id', $assigneeId))
            ->orderBy('priority', 'desc')
            ->orderBy('order')
            ->paginate(50);

        return Inertia::render('Stories/Index', [
            'project' => $project->load(['epics', 'sprints', 'members']),
            'stories' => $stories,
            'filters' => $request->only(['status', 'epic_id', 'sprint_id', 'assignee_id']),
        ]);
    }

    public function create(Request $request, Project $project): Response
    {
        return Inertia::render('Stories/Create', [
            'project' => $project->load(['epics', 'sprints', 'members']),
            'epicId' => $request->epic_id,
            'sprintId' => $request->sprint_id,
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'acceptance_criteria' => 'nullable|string',
            'estimated_hours' => 'required|numeric|min:0',
            'priority' => 'required|in:low,medium,high,critical',
            'epic_id' => 'nullable|exists:epics,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $validated['reporter_id'] = $request->user()->id;
        $validated['order'] = $project->stories()->max('order') + 1;

        $story = $project->stories()->create($validated);

        return redirect()->route('stories.show', [$project, $story])
            ->with('success', 'Story created successfully.');
    }

    public function show(Request $request, Project $project, Story $story): Response
    {
        $story->load([
            'assignee',
            'reporter',
            'epic',
            'sprint',
            'tasks' => fn ($q) => $q->orderBy('order'),
            'tasks.assignee',
            'tasks.timeLogs.user',
            'comments.user',
        ]);

        return Inertia::render('Stories/Show', [
            'project' => $project->load(['epics', 'sprints', 'members']),
            'story' => $story,
        ]);
    }

    public function edit(Project $project, Story $story): Response
    {
        return Inertia::render('Stories/Edit', [
            'project' => $project->load(['epics', 'sprints', 'members']),
            'story' => $story,
        ]);
    }

    public function update(Request $request, Project $project, Story $story): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'acceptance_criteria' => 'nullable|string',
            'estimated_hours' => 'required|numeric|min:0',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:backlog,todo,in_progress,review,done',
            'epic_id' => 'nullable|exists:epics,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $story->update($validated);

        return redirect()->route('stories.show', [$project, $story])
            ->with('success', 'Story updated successfully.');
    }

    public function destroy(Project $project, Story $story): RedirectResponse
    {
        $story->delete();

        return redirect()->route('stories.index', $project)
            ->with('success', 'Story deleted successfully.');
    }

    /**
     * Update story status (for Kanban drag & drop)
     */
    public function updateStatus(Request $request, Project $project, Story $story): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:backlog,todo,in_progress,review,done',
            'order' => 'nullable|integer|min:0',
        ]);

        $story->update($validated);

        return back();
    }

    /**
     * Reorder stories (for drag & drop)
     */
    public function reorder(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'stories' => 'required|array',
            'stories.*.id' => 'required|exists:stories,id',
            'stories.*.order' => 'required|integer|min:0',
            'stories.*.status' => 'required|in:backlog,todo,in_progress,review,done',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['stories'] as $storyData) {
                Story::where('id', $storyData['id'])->update([
                    'order' => $storyData['order'],
                    'status' => $storyData['status'],
                ]);
            }
        });

        return back();
    }

    /**
     * Move story to sprint
     */
    public function moveToSprint(Request $request, Project $project, Story $story): RedirectResponse
    {
        $validated = $request->validate([
            'sprint_id' => 'nullable|exists:sprints,id',
        ]);

        $story->update([
            'sprint_id' => $validated['sprint_id'],
            'status' => $validated['sprint_id'] ? 'todo' : 'backlog',
        ]);

        return back()->with('success', 'Story moved successfully.');
    }

    /**
     * Add comment to story
     */
    public function addComment(Request $request, Project $project, Story $story): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $story->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Comment added.');
    }
}
