<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Create a new controller instance.
     * Crea una nueva instancia del controlador.
     */
    public function __construct(
        protected TaskService $service
    ) {
        // Authorization is handled in each method / La autorización se maneja en cada método
    }

    /**
     * Display a listing of the resource.
     * Muestra un listado del recurso.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Task::class);

        $query = Task::query()
            ->forCurrentTeam()
            ->with(['project', 'creator', 'assignees', 'labels'])
            ->withCount(['assignees', 'labels', 'comments']);

        // Filter by project / Filtrar por proyecto
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status / Filtrar por estado
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority / Filtrar por prioridad
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assignee / Filtrar por asignado
        if ($request->has('assignee_id')) {
            $query->assignedTo($request->assignee_id);
        }

        // Search / Buscar
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by position for Kanban / Ordenar por posición para Kanban
        if ($request->has('order_by_position')) {
            $query->orderedByPosition();
        } else {
            $query->latest();
        }

        $tasks = $query->paginate($request->get('per_page', 15));

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        Gate::authorize('create', Task::class);

        $task = $this->service->create($request->validated(), $request->user());

        return response()->json([
            'message' => 'Tarea creada exitosamente. / Task created successfully.',
            'data' => new TaskResource($task->load(['project', 'creator', 'assignees', 'labels'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     * Muestra el recurso especificado.
     */
    public function show(Task $task, Request $request): JsonResponse|Response
    {
        Gate::authorize('view', $task);

        $task->load(['project', 'creator', 'assignees', 'labels']);
        $comments = $task->comments()
            ->with('user')
            ->latest()
            ->paginate(10);

        // Return Inertia response for web requests / Retornar respuesta Inertia para solicitudes web
        if ($request->wantsJson()) {
            return response()->json([
                'data' => new TaskResource($task),
            ]);
        }

        return Inertia::render('Tasks/Show', [
            'task' => new TaskResource($task),
            'comments' => $comments,
            'users' => User::where('current_team_id', auth()->user()->currentTeam->id)->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', $task);

        $task = $this->service->update($task, $request->validated(), $request->user());

        // Return JSON for API requests / Retornar JSON para solicitudes API
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tarea actualizada exitosamente. / Task updated successfully.',
                'data' => new TaskResource($task->load(['project.team', 'project.owner', 'creator', 'assignees', 'labels'])),
            ]);
        }

        // Return redirect for Inertia requests / Retornar redirect para solicitudes Inertia
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $this->service->delete($task);

        return response()->json([
            'message' => 'Tarea eliminada exitosamente. / Task deleted successfully.',
        ]);
    }

    /**
     * Restore the specified resource.
     * Restaura el recurso especificado.
     */
    public function restore(Task $task): JsonResponse
    {
        Gate::authorize('restore', $task);

        $this->service->restore($task);

        return response()->json([
            'message' => 'Tarea restaurada exitosamente. / Task restored successfully.',
            'data' => new TaskResource($task->fresh(['project', 'creator', 'assignees', 'labels'])),
        ]);
    }

    /**
     * Assign users to a task.
     * Asigna usuarios a una tarea.
     */
    public function assign(Request $request, Task $task): JsonResponse
    {
        Gate::authorize('assign', $task);

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->service->syncAssignees($task, $request->user_ids, $request->user());

        return response()->json([
            'message' => 'Usuarios asignados exitosamente. / Users assigned successfully.',
            'data' => new TaskResource($task->fresh(['assignees'])),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Muestra el formulario para crear un nuevo recurso.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Task::class);

        return Inertia::render('Tasks/Create', [
            'project_id' => $request->get('project_id'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(Task $task): Response
    {
        Gate::authorize('update', $task);

        $task->load(['project', 'creator', 'assignees', 'labels']);

        return Inertia::render('Tasks/Edit', [
            'task' => new TaskResource($task),
        ]);
    }
}
