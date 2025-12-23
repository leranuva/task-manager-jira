<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Label>
 */
class LabelFactory extends Factory
{
    /**
     * Define the model's default state.
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#06B6D4', '#6B7280'];
        $labelNames = ['urgent', 'bug', 'feature', 'enhancement', 'documentation', 'question', 'help wanted', 'good first issue'];

        return [
            'team_id' => \App\Models\Team::factory(),
            'project_id' => null, // Global label by default / Etiqueta global por defecto
            'name' => fake()->randomElement($labelNames),
            'color' => fake()->randomElement($colors),
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the label is project-specific.
     * Indica que la etiqueta es específica del proyecto.
     */
    public function forProject(\App\Models\Project $project): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $project->team_id,
            'project_id' => $project->id,
        ]);
    }
}
