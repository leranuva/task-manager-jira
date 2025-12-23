<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4'];
        $icons = ['briefcase', 'rocket', 'code', 'palette', 'chart', 'star', 'heart'];
        
        $name = fake()->words(2, true);
        
        // Generate project key from name (max 4 chars) / Generar clave de proyecto desde el nombre (máx 4 caracteres)
        $baseKey = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $name), 0, 4));
        if (strlen($baseKey) < 4) {
            $baseKey = strtoupper(fake()->lexify('????'));
        }

        return [
            'team_id' => \App\Models\Team::factory(),
            'owner_id' => \App\Models\User::factory(),
            'name' => ucwords($name),
            'key' => $baseKey, // Will be made unique by Sequence / Será único mediante Sequence
            'description' => fake()->paragraph(),
            'color' => fake()->randomElement($colors),
            'icon' => fake()->randomElement($icons),
            'is_active' => true,
            'settings' => [
                'default_task_type' => fake()->randomElement(['task', 'bug', 'feature']),
                'default_priority' => fake()->randomElement(['low', 'medium', 'high']),
            ],
        ];
    }

    /**
     * Configure the model factory.
     * Configura la factoría del modelo.
     */
    public function configure(): static
    {
        return $this->sequence(
            fn ($sequence) => [
                // Generate unique key using sequence / Generar clave única usando secuencia
                'key' => $this->generateUniqueKey($sequence->index + 1),
            ]
        );
    }

    /**
     * Generate a unique project key.
     * Genera una clave única de proyecto.
     * 
     * @param int $index
     * @return string
     */
    private function generateUniqueKey(int $index): string
    {
        // Try to use the base key from definition / Intentar usar la clave base de la definición
        $baseKey = $this->faker->lexify('????');
        
        // Ensure uniqueness by checking database / Asegurar unicidad verificando la base de datos
        $key = strtoupper($baseKey);
        $counter = 1;
        
        while (\App\Models\Project::where('key', $key)->exists()) {
            $key = strtoupper($baseKey) . $counter;
            $counter++;
        }
        
        return $key;
    }

    /**
     * Indicate that the project is inactive.
     * Indica que el proyecto está inactivo.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
