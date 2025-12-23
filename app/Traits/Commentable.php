<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    /**
     * Get all comments for this model.
     * Obtiene todos los comentarios de este modelo.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the latest comments for this model.
     * Obtiene los comentarios más recientes de este modelo.
     * 
     * @param int $limit Maximum number of comments to retrieve
     * @param int $limit Número máximo de comentarios a recuperar
     */
    public function latestComments(int $limit = 10)
    {
        return $this->comments()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }
}

