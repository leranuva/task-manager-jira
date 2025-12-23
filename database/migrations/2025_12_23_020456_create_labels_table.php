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
        Schema::create('labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->default('#6B7280'); // Color hexadecimal
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['team_id', 'name', 'project_id']);
            $table->index(['team_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
