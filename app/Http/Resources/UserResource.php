<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'profile_photo_url' => $this->profile_photo_url,
            
            // Only include if explicitly requested / Solo incluir si se solicita explícitamente
            'current_team' => $this->when($request->user()?->id === $this->id, function () {
                return [
                    'id' => $this->currentTeam?->id,
                    'name' => $this->currentTeam?->name,
                ];
            }),
            
            // Timestamps / Marcas de tiempo
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
