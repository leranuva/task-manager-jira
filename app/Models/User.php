<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the roles for this user in a specific team.
     * Obtiene los roles de este usuario en un equipo específico.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot('team_id')
            ->withTimestamps();
    }

    /**
     * Get all permissions for this user (through roles).
     * Obtiene todos los permisos de este usuario (a través de roles).
     */
    public function permissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    /**
     * Check if the user has a specific role in a team.
     * Verifica si el usuario tiene un rol específico en un equipo.
     * 
     * @param string $roleName The role name to check
     * @param string $roleName El nombre del rol a verificar
     * @param int|null $teamId The team ID (null = current team)
     * @param int|null $teamId El ID del equipo (null = equipo actual)
     */
    public function hasRole(string $roleName, ?int $teamId = null): bool
    {
        $query = $this->roles()->where('name', $roleName);
        
        if ($teamId) {
            $query->wherePivot('team_id', $teamId);
        } else {
            $query->wherePivot('team_id', $this->currentTeam?->id);
        }

        return $query->exists();
    }

    /**
     * Check if the user has a specific permission.
     * Verifica si el usuario tiene un permiso específico.
     * 
     * @param string $permissionName The permission name to check
     * @param string $permissionName El nombre del permiso a verificar
     * @param int|null $teamId The team ID (null = current team)
     * @param int|null $teamId El ID del equipo (null = equipo actual)
     */
    public function hasPermission(string $permissionName, ?int $teamId = null): bool
    {
        $teamId = $teamId ?? $this->currentTeam?->id;
        
        return $this->roles()
            ->wherePivot('team_id', $teamId)
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    /**
     * Get all projects owned by this user.
     * Obtiene todos los proyectos propiedad de este usuario.
     */
    public function ownedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Get all tasks created by this user.
     * Obtiene todas las tareas creadas por este usuario.
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    /**
     * Get all tasks assigned to this user.
     * Obtiene todas las tareas asignadas a este usuario.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignments', 'user_id', 'task_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /**
     * Get all comments created by this user.
     * Obtiene todos los comentarios creados por este usuario.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
