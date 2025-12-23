<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'key' => $this->key,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'story_points' => $this->story_points,
            'due_date' => $this->due_date?->toDateString(),
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'position' => $this->position,
            'metadata' => $this->metadata,
            
            // Relationships / Relaciones
            'project' => new ProjectResource($this->whenLoaded('project')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'assignees' => $this->whenLoaded('assignees') 
                ? UserResource::collection($this->assignees)->resolve() 
                : [],
            'labels' => $this->whenLoaded('labels') 
                ? LabelResource::collection($this->labels)->resolve() 
                : [],
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            
            // Counts / Conteos
            'assignees_count' => $this->whenCounted('assignees'),
            'labels_count' => $this->whenCounted('labels'),
            'comments_count' => $this->whenCounted('comments'),
            
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
