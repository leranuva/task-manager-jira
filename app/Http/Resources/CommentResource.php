<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'is_edited' => $this->is_edited,
            
            // Relationships / Relaciones
            'user' => new UserResource($this->whenLoaded('user')),
            'parent' => new CommentResource($this->whenLoaded('parent')),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'commentable' => $this->when($this->commentable, function () {
                return [
                    'id' => $this->commentable->id,
                    'type' => class_basename($this->commentable),
                    'title' => $this->commentable->title ?? $this->commentable->name ?? null,
                ];
            }),
            
            // Counts / Conteos
            'replies_count' => $this->whenCounted('replies'),
            
            // Team info / Información del equipo
            'team' => [
                'id' => $this->team_id,
            ],
            
            // Timestamps / Marcas de tiempo
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
