<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('key')->unique(); // PROJ-123, DEV-456 (como en Jira)
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'in_review', 'done', 'cancelled'])->default('todo');
            $table->enum('priority', ['lowest', 'low', 'medium', 'high', 'highest'])->default('medium');
            $table->enum('type', ['task', 'bug', 'feature', 'epic', 'story'])->default('task');
            $table->integer('story_points')->nullable(); // Puntos de historia (Scrum)
            $table->date('due_date')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('position')->default(0); // Para ordenamiento en Kanban
            $table->json('metadata')->nullable(); // Datos adicionales flexibles
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'project_id']);
            $table->index(['team_id', 'status']);
            $table->index(['project_id', 'status', 'position']);
            $table->index('creator_id');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
