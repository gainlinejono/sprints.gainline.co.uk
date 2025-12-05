<?php

namespace App\Http\Controllers;

use App\Models\Epic;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EpicController extends Controller
{
    public function index(Request $request, Project $project): Response
    {
        $epics = $project->epics()
            ->withCount(['stories'])
            ->orderBy('priority', 'desc')
            ->get();

        return Inertia::render('Epics/Index', [
            'project' => $project,
            'epics' => $epics,
        ]);
    }

    public function create(Project $project): Response
    {
        return Inertia::render('Epics/Create', [
            'project' => $project,
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'priority' => 'required|integer|min:0',
            'target_date' => 'nullable|date',
        ]);

        $epic = $project->epics()->create($validated);

        return redirect()->route('epics.show', [$project, $epic])
            ->with('success', 'Epic created successfully.');
    }

    public function show(Request $request, Project $project, Epic $epic): Response
    {
        $epic->load([
            'stories' => fn ($q) => $q->orderBy('priority', 'desc')->orderBy('order'),
            'stories.assignee',
            'stories.sprint',
        ]);

        return Inertia::render('Epics/Show', [
            'project' => $project,
            'epic' => $epic,
        ]);
    }

    public function edit(Project $project, Epic $epic): Response
    {
        return Inertia::render('Epics/Edit', [
            'project' => $project,
            'epic' => $epic,
        ]);
    }

    public function update(Request $request, Project $project, Epic $epic): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'priority' => 'required|integer|min:0',
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'target_date' => 'nullable|date',
        ]);

        $epic->update($validated);

        return redirect()->route('epics.show', [$project, $epic])
            ->with('success', 'Epic updated successfully.');
    }

    public function destroy(Project $project, Epic $epic): RedirectResponse
    {
        // Unlink stories from epic (don't delete them)
        $epic->stories()->update(['epic_id' => null]);

        $epic->delete();

        return redirect()->route('epics.index', $project)
            ->with('success', 'Epic deleted successfully.');
    }
}
