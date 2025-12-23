<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaskAssignment>
 */
class TaskAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $task = \App\Models\Task::factory()->create();
        $user = \App\Models\User::factory()->create();

        return [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'assigned_by' => \App\Models\User::factory(),
        ];
    }
}
