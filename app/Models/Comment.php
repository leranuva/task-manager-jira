<?php

namespace App\Models;

use App\Traits\BelongsToTeam;
use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, HasUuids, BelongsToTeam, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'commentable_id',
        'commentable_type',
        'body',
        'parent_id',
        'is_edited',
    ];

    /**
     * Get the attributes that should be cast.
     * Obtiene los atributos que deben ser convertidos.
     */
    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
        ];
    }

    /**
     * Get the team that owns this comment.
     * Obtiene el equipo que posee este comentario.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who created this comment.
     * Obtiene el usuario que creó este comentario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent model (Task, Project, etc.).
     * Obtiene el modelo padre (Task, Project, etc.) - Relación polimórfica.
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent comment (for nested comments).
     * Obtiene el comentario padre (para comentarios anidados/respuestas).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all replies to this comment.
     * Obtiene todas las respuestas a este comentario.
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
