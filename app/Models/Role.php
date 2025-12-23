<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Los atributos que son asignables en masa.
     */
    protected $fillable = [
        'team_id',
        'name',
        'display_name',
        'description',
        'is_system',
    ];

    /**
     * Get the attributes that should be cast.
     * Obtiene los atributos que deben ser convertidos.
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    /**
     * Get the team that owns this role.
     * Obtiene el equipo que posee este rol.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the permissions for this role.
     * Obtiene los permisos de este rol.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Get the users that have this role.
     * Obtiene los usuarios que tienen este rol.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot('team_id')
            ->withTimestamps();
    }

    /**
     * Check if the role has a specific permission.
     * Verifica si el rol tiene un permiso específico.
     * 
     * @param string $permissionName The permission name to check
     * @param string $permissionName El nombre del permiso a verificar
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()
            ->where('name', $permissionName)
            ->exists();
    }
}
