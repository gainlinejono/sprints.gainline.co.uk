<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $projects = $this->getAccessibleProjects($user)
            ->load(['members', 'sprints' => fn ($q) => $q->where('status', 'active')]);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'key' => 'required|string|max:10|unique:projects,key|alpha_num|uppercase',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $project = Project::create($validated);

        // Add creator as admin
        $project->members()->attach($request->user()->id, ['role' => 'admin']);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Request $request, Project $project): Response
    {
        $this->authorizeProject($request->user(), $project);

        $project->load([
            'members',
            'epics' => fn ($q) => $q->orderBy('priority', 'desc'),
            'sprints' => fn ($q) => $q->orderBy('start_date', 'desc'),
        ]);

        $activeSprint = $project->sprints->firstWhere('status', 'active');
        $backlogStories = $project->stories()
            ->backlog()
            ->with(['assignee', 'reporter', 'epic'])
            ->orderBy('priority', 'desc')
            ->orderBy('order')
            ->get();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'activeSprint' => $activeSprint?->load(['stories.assignee', 'stories.tasks']),
            'backlogStories' => $backlogStories,
        ]);
    }

    public function edit(Request $request, Project $project): Response
    {
        $this->authorizeProject($request->user(), $project, 'admin');

        return Inertia::render('Projects/Edit', [
            'project' => $project->load('members'),
            'users' => User::all(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($request->user(), $project, 'admin');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'key' => ['required', 'string', 'max:10', 'alpha_num', 'uppercase', Rule::unique('projects')->ignore($project->id)],
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($request->user(), $project, 'admin');

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Manage project members
     */
    public function members(Request $request, Project $project): Response
    {
        $this->authorizeProject($request->user(), $project, 'admin');

        return Inertia::render('Projects/Members', [
            'project' => $project->load('members'),
            'availableUsers' => User::whereNotIn('id', $project->members->pluck('id'))->get(),
        ]);
    }

    public function addMember(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($request->user(), $project, 'admin');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,member,viewer',
        ]);

        $project->members()->attach($validated['user_id'], ['role' => $validated['role']]);

        return back()->with('success', 'Member added successfully.');
    }

    public function removeMember(Request $request, Project $project, User $user): RedirectResponse
    {
        $this->authorizeProject($request->user(), $project, 'admin');

        $project->members()->detach($user->id);

        return back()->with('success', 'Member removed successfully.');
    }

    /**
     * Get projects accessible to the user
     */
    private function getAccessibleProjects($user)
    {
        if ($user->isGainlineUser()) {
            return Project::where('is_active', true)
                ->withCount(['sprints', 'stories', 'epics', 'members'])
                ->orderBy('name')
                ->get();
        }

        return $user->projects()
            ->where('is_active', true)
            ->withCount(['sprints', 'stories', 'epics', 'members'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Authorize user access to project
     */
    private function authorizeProject($user, Project $project, ?string $requiredRole = null): void
    {
        // Gainline users have access to all projects
        if ($user->isGainlineUser()) {
            return;
        }

        $member = $project->members()->where('user_id', $user->id)->first();

        if (!$member) {
            abort(403, 'You do not have access to this project.');
        }

        if ($requiredRole === 'admin' && $member->pivot->role !== 'admin') {
            abort(403, 'You do not have permission to perform this action.');
        }
    }
}
