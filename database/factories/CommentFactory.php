<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create a random commentable model (Task or Project)
        // Crea un modelo comentable aleatorio (Task o Project)
        $commentable = fake()->randomElement([
            \App\Models\Task::factory(),
            \App\Models\Project::factory(),
        ])->create();

        return [
            'team_id' => $commentable->team_id,
            'user_id' => \App\Models\User::factory(),
            'commentable_id' => $commentable->id,
            'commentable_type' => get_class($commentable),
            'body' => fake()->paragraph(),
            'parent_id' => null,
            'is_edited' => false,
        ];
    }

    /**
     * Indicate that the comment is a reply to another comment.
     * Indica que el comentario es una respuesta a otro comentario.
     */
    public function reply(?\App\Models\Comment $parent = null): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            $parentComment = $parent ?? \App\Models\Comment::factory()->create([
                'team_id' => $attributes['team_id'],
                'commentable_id' => $attributes['commentable_id'],
                'commentable_type' => $attributes['commentable_type'],
            ]);

            return [
                'parent_id' => $parentComment->id,
                'team_id' => $parentComment->team_id,
                'commentable_id' => $parentComment->commentable_id,
                'commentable_type' => $parentComment->commentable_type,
            ];
        });
    }

    /**
     * Indicate that the comment has been edited.
     * Indica que el comentario ha sido editado.
     */
    public function edited(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_edited' => true,
        ]);
    }
}
