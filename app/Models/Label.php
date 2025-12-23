<?php

namespace App\Models;

use App\Traits\BelongsToTeam;
use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Label extends Model
{
    use HasFactory, HasUuids, BelongsToTeam, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'team_id',
        'project_id',
        'name',
        'color',
        'description',
    ];

    /**
     * Get the team that owns this label.
     * Obtiene el equipo que posee esta etiqueta.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the project that owns this label (if project-specific).
     * Obtiene el proyecto que posee esta etiqueta (si es específica del proyecto).
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get all tasks with this label.
     * Obtiene todas las tareas con esta etiqueta.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_label');
    }

    /**
     * Scope a query to only include labels for a specific project.
     * Scope para incluir solo etiquetas de un proyecto específico.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|int $projectId The project ID
     * @param string|int $projectId El ID del proyecto
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope a query to only include global labels (not project-specific).
     * Scope para incluir solo etiquetas globales (no específicas de proyecto).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('project_id');
    }
}
