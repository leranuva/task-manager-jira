<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'key' => $this->key,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'settings' => $this->settings,
            
            // Relationships / Relaciones
            'owner' => new UserResource($this->whenLoaded('owner')),
            'team' => $this->when($this->relationLoaded('team'), [
                'id' => $this->team_id,
                'name' => $this->team->name,
            ], [
                'id' => $this->team_id,
            ]),
            
            // Counts / Conteos
            'tasks_count' => $this->whenCounted('tasks'),
            'labels_count' => $this->whenCounted('labels'),
            'comments_count' => $this->whenCounted('comments'),
            
            // Timestamps / Marcas de tiempo
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
