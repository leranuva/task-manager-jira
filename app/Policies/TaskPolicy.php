<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     * Determina si el usuario puede ver cualquier modelo.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('task.view', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can view the model.
     * Determina si el usuario puede ver el modelo.
     */
    public function view(User $user, Task $task): bool
    {
        // Check if task belongs to user's current team / Verificar si la tarea pertenece al equipo actual del usuario
        if ($task->team_id !== $user->currentTeam?->id) {
            return false;
        }

        return $user->hasPermission('task.view', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can create models.
     * Determina si el usuario puede crear modelos.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('task.create', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can update the model.
     * Determina si el usuario puede actualizar el modelo.
     */
    public function update(User $user, Task $task): bool
    {
        // Check if task belongs to user's current team / Verificar si la tarea pertenece al equipo actual del usuario
        if ($task->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Creator can always update / El creador siempre puede actualizar
        if ($task->creator_id === $user->id) {
            return true;
        }

        // Assigned user can update / El usuario asignado puede actualizar
        if ($task->assignees()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return $user->hasPermission('task.update', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can delete the model.
     * Determina si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, Task $task): bool
    {
        // Check if task belongs to user's current team / Verificar si la tarea pertenece al equipo actual del usuario
        if ($task->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Creator can always delete / El creador siempre puede eliminar
        if ($task->creator_id === $user->id) {
            return true;
        }

        return $user->hasPermission('task.delete', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can assign the task.
     * Determina si el usuario puede asignar la tarea.
     */
    public function assign(User $user, Task $task): bool
    {
        // Check if task belongs to user's current team / Verificar si la tarea pertenece al equipo actual del usuario
        if ($task->team_id !== $user->currentTeam?->id) {
            return false;
        }

        return $user->hasPermission('task.assign', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can restore the model.
     * Determina si el usuario puede restaurar el modelo.
     */
    public function restore(User $user, Task $task): bool
    {
        // Check if task belongs to user's current team / Verificar si la tarea pertenece al equipo actual del usuario
        if ($task->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Creator can always restore / El creador siempre puede restaurar
        if ($task->creator_id === $user->id) {
            return true;
        }

        return $user->hasPermission('task.delete', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Determina si el usuario puede eliminar permanentemente el modelo.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        // Check if task belongs to user's current team / Verificar si la tarea pertenece al equipo actual del usuario
        if ($task->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Only creator can force delete / Solo el creador puede eliminar permanentemente
        return $task->creator_id === $user->id;
    }
}
