<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeam
{
    /**
     * Get the team that owns the model.
     * Obtiene el equipo que posee el modelo.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope a query to only include models for a specific team.
     * Scope para incluir solo modelos de un equipo específico.
     */
    public function scopeForTeam(Builder $query, $teamId): Builder
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope a query to only include models for the current user's team.
     * Scope para incluir solo modelos del equipo actual del usuario.
     */
    public function scopeForCurrentTeam(Builder $query): Builder
    {
        $teamId = auth()->user()?->currentTeam?->id;
        
        // Return empty result if no team
        // Retorna resultado vacío si no hay equipo
        if (!$teamId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('team_id', $teamId);
    }
}

