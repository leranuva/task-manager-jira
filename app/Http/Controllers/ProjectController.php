<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Policies\ProjectPolicy;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Create a new controller instance.
     * Crea una nueva instancia del controlador.
     */
    public function __construct(
        protected ProjectService $service
    ) {
        // Authorization is handled in each method / La autorización se maneja en cada método
    }

    /**
     * Display a listing of the resource.
     * Muestra un listado del recurso.
     */
    public function index(Request $request): Response|AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Project::class);

        $query = Project::query()
            ->forCurrentTeam()
            ->with(['owner', 'team'])
            ->withCount(['tasks', 'labels', 'comments']);

        // Filter by active status / Filtrar por estado activo
        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN));
        }

        // Search by name or key / Buscar por nombre o clave
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate($request->get('per_page', 15));

        // Return Inertia response for web requests / Retornar respuesta Inertia para solicitudes web
        if ($request->wantsJson()) {
            return ProjectResource::collection($projects);
        }

        return Inertia::render('Projects/Index', [
            'projects' => ProjectResource::collection($projects),
            'filters' => $request->only(['search', 'active']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        Gate::authorize('create', Project::class);

        $project = $this->service->create($request->validated(), $request->user());

        return response()->json([
            'message' => 'Proyecto creado exitosamente. / Project created successfully.',
            'data' => new ProjectResource($project->load(['owner', 'team'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     * Muestra el recurso especificado.
     */
    public function show(Project $project): JsonResponse
    {
        Gate::authorize('view', $project);

        $project->load(['owner', 'team', 'tasks', 'labels', 'comments']);

        return response()->json([
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('update', $project);

        $project = $this->service->update($project, $request->validated());

        return response()->json([
            'message' => 'Proyecto actualizado exitosamente. / Project updated successfully.',
            'data' => new ProjectResource($project->load(['owner', 'team'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(Project $project): JsonResponse
    {
        Gate::authorize('delete', $project);

        $this->service->delete($project);

        return response()->json([
            'message' => 'Proyecto eliminado exitosamente. / Project deleted successfully.',
        ]);
    }

    /**
     * Restore the specified resource.
     * Restaura el recurso especificado.
     */
    public function restore(Project $project): JsonResponse
    {
        Gate::authorize('restore', $project);

        $this->service->restore($project);

        return response()->json([
            'message' => 'Proyecto restaurado exitosamente. / Project restored successfully.',
            'data' => new ProjectResource($project->fresh(['owner', 'team'])),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Muestra el formulario para crear un nuevo recurso.
     */
    public function create(): Response
    {
        Gate::authorize('create', Project::class);

        return Inertia::render('Projects/Create');
    }

    /**
     * Show the form for editing the specified resource.
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(Project $project): Response
    {
        Gate::authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'project' => new ProjectResource($project->load(['owner', 'team'])),
        ]);
    }

    /**
     * Display the Kanban board for a project.
     * Muestra el tablero Kanban de un proyecto.
     */
    public function kanban(Project $project, Request $request): Response
    {
        Gate::authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['creator', 'assignees', 'labels'])
            ->withCount(['assignees', 'labels', 'comments'])
            ->orderedByPosition()
            ->get();

        return Inertia::render('Projects/Kanban', [
            'project' => new ProjectResource($project->load(['owner', 'team'])),
            'tasks' => TaskResource::collection($tasks)->resolve(), // Convert to array / Convertir a array
        ]);
    }
}
