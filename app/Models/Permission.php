<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'name',
        'display_name',
        'group',
        'description',
    ];

    /**
     * Get the roles that have this permission.
     * Obtiene los roles que tienen este permiso.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Scope a query to only include permissions for a specific group.
     * Scope para incluir solo permisos de un grupo específico.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $group The permission group (projects, tasks, comments, teams)
     * @param string $group El grupo de permisos (projects, tasks, comments, teams)
     */
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
