<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     * Determina si el usuario puede ver cualquier modelo.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('project.view', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can view the model.
     * Determina si el usuario puede ver el modelo.
     */
    public function view(User $user, Project $project): bool
    {
        // Check if project belongs to user's current team / Verificar si el proyecto pertenece al equipo actual del usuario
        if ($project->team_id !== $user->currentTeam?->id) {
            return false;
        }

        return $user->hasPermission('project.view', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can create models.
     * Determina si el usuario puede crear modelos.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('project.create', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can update the model.
     * Determina si el usuario puede actualizar el modelo.
     */
    public function update(User $user, Project $project): bool
    {
        // Check if project belongs to user's current team / Verificar si el proyecto pertenece al equipo actual del usuario
        if ($project->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Owner can always update / El propietario siempre puede actualizar
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $user->hasPermission('project.update', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can delete the model.
     * Determina si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, Project $project): bool
    {
        // Check if project belongs to user's current team / Verificar si el proyecto pertenece al equipo actual del usuario
        if ($project->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Owner can always delete / El propietario siempre puede eliminar
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $user->hasPermission('project.delete', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can restore the model.
     * Determina si el usuario puede restaurar el modelo.
     */
    public function restore(User $user, Project $project): bool
    {
        // Check if project belongs to user's current team / Verificar si el proyecto pertenece al equipo actual del usuario
        if ($project->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Only owner or admin can restore / Solo el propietario o admin puede restaurar
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $user->hasPermission('project.delete', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Determina si el usuario puede eliminar permanentemente el modelo.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        // Check if project belongs to user's current team / Verificar si el proyecto pertenece al equipo actual del usuario
        if ($project->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Only owner can force delete / Solo el propietario puede eliminar permanentemente
        return $project->owner_id === $user->id;
    }
}
