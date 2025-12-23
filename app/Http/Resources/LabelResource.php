<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabelResource extends JsonResource
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
            'color' => $this->color,
            'description' => $this->description,
            'is_global' => is_null($this->project_id),
            
            // Relationships / Relaciones
            'project' => new ProjectResource($this->whenLoaded('project')),
            
            // Counts / Conteos
            'tasks_count' => $this->whenCounted('tasks'),
            
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
