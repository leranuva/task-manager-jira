<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = [
            ['name' => 'owner', 'display_name' => 'Propietario', 'description' => 'Propietario del equipo con todos los permisos'],
            ['name' => 'admin', 'display_name' => 'Administrador', 'description' => 'Administrador con permisos de gestión'],
            ['name' => 'member', 'display_name' => 'Miembro', 'description' => 'Miembro del equipo con permisos básicos'],
            ['name' => 'viewer', 'display_name' => 'Visualizador', 'description' => 'Solo puede ver, sin permisos de edición'],
        ];

        $role = fake()->randomElement($roles);

        return [
            'team_id' => null, // Global role / Rol global
            'name' => $role['name'],
            'display_name' => $role['display_name'],
            'description' => $role['description'],
            'is_system' => true,
        ];
    }

    /**
     * Indicate that the role is team-specific.
     * Indica que el rol es específico del equipo.
     */
    public function forTeam(int $teamId): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $teamId,
            'is_system' => false,
        ]);
    }
}
