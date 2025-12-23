<?php

namespace App\Models;

use App\Traits\BelongsToTeam;
use App\Traits\Commentable;
use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuids, BelongsToTeam, Commentable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'team_id',
        'owner_id',
        'name',
        'key',
        'description',
        'color',
        'icon',
        'is_active',
        'settings',
    ];

    /**
     * Get the attributes that should be cast.
     * Obtiene los atributos que deben ser convertidos.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Get the team that owns this project.
     * Obtiene el equipo que posee este proyecto.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who owns this project.
     * Obtiene el usuario que posee este proyecto.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all tasks for this project.
     * Obtiene todas las tareas de este proyecto.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get all labels for this project.
     * Obtiene todas las etiquetas de este proyecto.
     */
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    /**
     * Scope a query to only include active projects.
     * Scope para incluir solo proyectos activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
