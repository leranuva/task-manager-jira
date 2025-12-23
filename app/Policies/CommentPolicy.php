<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     * Determina si el usuario puede ver cualquier modelo.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('comment.view', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can view the model.
     * Determina si el usuario puede ver el modelo.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Check if comment belongs to user's current team / Verificar si el comentario pertenece al equipo actual del usuario
        if ($comment->team_id !== $user->currentTeam?->id) {
            return false;
        }

        return $user->hasPermission('comment.view', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can create models.
     * Determina si el usuario puede crear modelos.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('comment.create', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can update the model.
     * Determina si el usuario puede actualizar el modelo.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Check if comment belongs to user's current team / Verificar si el comentario pertenece al equipo actual del usuario
        if ($comment->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Author can always update / El autor siempre puede actualizar
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->hasPermission('comment.update', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can delete the model.
     * Determina si el usuario puede eliminar el modelo.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Check if comment belongs to user's current team / Verificar si el comentario pertenece al equipo actual del usuario
        if ($comment->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Author can always delete / El autor siempre puede eliminar
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->hasPermission('comment.delete', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can restore the model.
     * Determina si el usuario puede restaurar el modelo.
     */
    public function restore(User $user, Comment $comment): bool
    {
        // Check if comment belongs to user's current team / Verificar si el comentario pertenece al equipo actual del usuario
        if ($comment->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Author can always restore / El autor siempre puede restaurar
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->hasPermission('comment.delete', $user->currentTeam?->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Determina si el usuario puede eliminar permanentemente el modelo.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        // Check if comment belongs to user's current team / Verificar si el comentario pertenece al equipo actual del usuario
        if ($comment->team_id !== $user->currentTeam?->id) {
            return false;
        }

        // Only author can force delete / Solo el autor puede eliminar permanentemente
        return $comment->user_id === $user->id;
    }
}
