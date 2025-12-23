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
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('commentable_id');
            $table->string('commentable_type'); // Task, Project, etc.
            $table->text('body');
            $table->uuid('parent_id')->nullable(); // Para comentarios anidados/replies
            $table->boolean('is_edited')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'commentable_id', 'commentable_type']);
            $table->index('user_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
