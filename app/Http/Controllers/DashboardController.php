<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Story;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get accessible projects
        $projects = $this->getAccessibleProjects($user);

        // Get active sprints
        $activeSprints = Sprint::whereIn('project_id', $projects->pluck('id'))
            ->where('status', 'active')
            ->with(['project', 'stories.assignee'])
            ->get();

        // Get user's assigned stories
        $myStories = Story::where('assignee_id', $user->id)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->with(['project', 'sprint', 'epic'])
            ->orderBy('priority', 'desc')
            ->limit(10)
            ->get();

        // Calculate stats
        $stats = [
            'total_projects' => $projects->count(),
            'active_sprints' => $activeSprints->count(),
            'my_stories' => $myStories->count(),
            'completed_today' => Story::where('assignee_id', $user->id)
                ->where('status', 'done')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        return Inertia::render('Dashboard', [
            'projects' => $projects,
            'activeSprints' => $activeSprints,
            'myStories' => $myStories,
            'stats' => $stats,
        ]);
    }

    /**
     * Get projects accessible to the user
     */
    private function getAccessibleProjects($user)
    {
        // Gainline users have access to all projects
        if ($user->isGainlineUser()) {
            return Project::where('is_active', true)
                ->withCount(['sprints', 'stories', 'epics'])
                ->get();
        }

        // Other users only see projects they're members of
        return $user->projects()
            ->where('is_active', true)
            ->withCount(['sprints', 'stories', 'epics'])
            ->get();
    }
}
