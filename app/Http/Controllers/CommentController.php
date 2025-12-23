<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Create a new controller instance.
     * Crea una nueva instancia del controlador.
     */
    public function __construct()
    {
        // Authorization is handled in each method / La autorización se maneja en cada método
    }

    /**
     * Display a listing of the resource.
     * Muestra un listado del recurso.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Comment::class);

        $query = Comment::query()
            ->forCurrentTeam()
            ->with(['user', 'parent', 'replies'])
            ->withCount(['replies']);

        // Filter by commentable / Filtrar por comentable
        if ($request->has('commentable_type') && $request->has('commentable_id')) {
            $query->where('commentable_type', $request->commentable_type)
                ->where('commentable_id', $request->commentable_id);
        }

        // Only top-level comments / Solo comentarios de nivel superior
        if ($request->has('top_level_only')) {
            $query->whereNull('parent_id');
        }

        $comments = $query->latest()->paginate($request->get('per_page', 15));

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created resource in storage.
     * Almacena un recurso recién creado en el almacenamiento.
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        Gate::authorize('create', Comment::class);

        // Verify commentable exists and belongs to team / Verificar que el comentable existe y pertenece al equipo
        $commentableType = str_replace('\\\\', '\\', $request->commentable_type);
        $commentable = $commentableType::where('id', $request->commentable_id)
            ->where('team_id', $request->user()->currentTeam?->id)
            ->firstOrFail();

        $data = $request->validated();
        $data['commentable_type'] = $commentableType;
        $data['team_id'] = $request->user()->currentTeam->id;
        $data['user_id'] = $request->user()->id;

        $comment = Comment::create($data);

        return response()->json([
            'message' => 'Comentario creado exitosamente. / Comment created successfully.',
            'data' => new CommentResource($comment->load(['user', 'parent', 'commentable'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     * Muestra el recurso especificado.
     */
    public function show(Comment $comment): JsonResponse
    {
        Gate::authorize('view', $comment);

        $comment->load(['user', 'parent', 'replies', 'commentable']);

        return response()->json([
            'data' => new CommentResource($comment),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza el recurso especificado en el almacenamiento.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        Gate::authorize('update', $comment);

        $request->validate([
            'body' => 'required|string|min:1|max:10000',
        ]);

        $comment->update([
            'body' => $request->body,
            'is_edited' => true,
        ]);

        return response()->json([
            'message' => 'Comentario actualizado exitosamente. / Comment updated successfully.',
            'data' => new CommentResource($comment->fresh(['user', 'parent'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina el recurso especificado del almacenamiento.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comentario eliminado exitosamente. / Comment deleted successfully.',
        ]);
    }
}
