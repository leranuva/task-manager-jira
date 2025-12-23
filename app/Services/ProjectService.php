<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * Create a new project.
     * Crea un nuevo proyecto.
     *
     * @param array<string, mixed> $data
     * @param User $user
     * @return Project
     */
    public function create(array $data, User $user): Project
    {
        // Ensure team_id and owner_id are set / Asegurar que team_id y owner_id estén establecidos
        $data['team_id'] = $data['team_id'] ?? $user->currentTeam?->id;
        $data['owner_id'] = $data['owner_id'] ?? $user->id;

        // Generate key if not provided / Generar clave si no se proporciona
        if (empty($data['key'])) {
            $data['key'] = $this->generateKey($data['name'] ?? 'PROJ');
        }

        // Set defaults / Establecer valores por defecto
        $data['is_active'] = $data['is_active'] ?? true;
        $data['color'] = $data['color'] ?? '#3B82F6';

        $project = Project::create($data);

        return $project->load(['owner', 'team']);
    }

    /**
     * Update a project.
     * Actualiza un proyecto.
     *
     * @param Project $project
     * @param array<string, mixed> $data
     * @return Project
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(['owner', 'team']);
    }

    /**
     * Delete a project (soft delete).
     * Elimina un proyecto (soft delete).
     *
     * @param Project $project
     * @return bool
     */
    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    /**
     * Restore a deleted project.
     * Restaura un proyecto eliminado.
     *
     * @param Project $project
     * @return bool
     */
    public function restore(Project $project): bool
    {
        return $project->restore();
    }

    /**
     * Generate a unique project key.
     * Genera una clave única de proyecto.
     *
     * @param string $name
     * @return string
     */
    private function generateKey(string $name): string
    {
        // Extract first 4 uppercase letters/numbers from name / Extraer primeras 4 letras/números mayúsculas del nombre
        $baseKey = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $name), 0, 4));
        
        if (strlen($baseKey) < 4) {
            $baseKey = strtoupper(Str::random(4));
        }

        // Ensure uniqueness / Asegurar unicidad
        $key = $baseKey;
        $counter = 1;
        
        while (Project::where('key', $key)->exists()) {
            $key = $baseKey . $counter;
            $counter++;
        }

        return $key;
    }
}

