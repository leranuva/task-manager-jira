<?php

namespace App\Models;

use App\Traits\BelongsToTeam;
use App\Traits\Commentable;
use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, BelongsToTeam, Commentable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'team_id',
        'project_id',
        'creator_id',
        'key',
        'title',
        'description',
        'status',
        'priority',
        'type',
        'story_points',
        'due_date',
        'started_at',
        'completed_at',
        'position',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     * Obtiene los atributos que deben ser convertidos.
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'story_points' => 'integer',
            'position' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the team that owns this task.
     * Obtiene el equipo que posee esta tarea.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the project that owns this task.
     * Obtiene el proyecto que posee esta tarea.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created this task.
     * Obtiene el usuario que creó esta tarea.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get all assignments for this task.
     * Obtiene todas las asignaciones de esta tarea.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    /**
     * Get all users assigned to this task.
     * Obtiene todos los usuarios asignados a esta tarea.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /**
     * Get all labels for this task.
     * Obtiene todas las etiquetas de esta tarea.
     */
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'task_label');
    }

    /**
     * Scope a query to only include tasks with a specific status.
     * Scope para incluir solo tareas con un estado específico.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status The task status (todo, in_progress, in_review, done, cancelled)
     * @param string $status El estado de la tarea (todo, in_progress, in_review, done, cancelled)
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include tasks with a specific priority.
     * Scope para incluir solo tareas con una prioridad específica.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $priority The task priority (lowest, low, medium, high, highest)
     * @param string $priority La prioridad de la tarea (lowest, low, medium, high, highest)
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include tasks assigned to a specific user.
     * Scope para incluir solo tareas asignadas a un usuario específico.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId The user ID
     * @param int $userId El ID del usuario
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->whereHas('assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope a query to order tasks by position.
     * Scope para ordenar tareas por posición (útil para Kanban).
     */
    public function scopeOrderedByPosition($query)
    {
        return $query->orderBy('position');
    }
}
