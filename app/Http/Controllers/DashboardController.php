<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     * Muestra el dashboard.
     */
    public function index(Request $request): Response
    {
        $teamId = $request->user()->currentTeam->id;

        // Get projects / Obtener proyectos
        $projects = Project::query()
            ->forCurrentTeam()
            ->with(['owner', 'team'])
            ->withCount(['tasks', 'labels', 'comments'])
            ->latest()
            ->limit(6)
            ->get();

        // Get recent tasks / Obtener tareas recientes
        $recentTasks = Task::query()
            ->forCurrentTeam()
            ->with(['project', 'creator', 'assignees'])
            ->latest()
            ->limit(10)
            ->get();

        // Calculate statistics / Calcular estadísticas
        $stats = [
            'total_projects' => Project::forCurrentTeam()->count(),
            'total_tasks' => Task::forCurrentTeam()->count(),
            'active_tasks' => Task::forCurrentTeam()
                ->whereIn('status', ['todo', 'in_progress', 'in_review'])
                ->count(),
            'completed_tasks' => Task::forCurrentTeam()
                ->where('status', 'done')
                ->count(),
        ];

        return Inertia::render('Dashboard', [
            'projects' => [
                'data' => $projects,
            ],
            'recentTasks' => $recentTasks,
            'stats' => $stats,
        ]);
    }
}

