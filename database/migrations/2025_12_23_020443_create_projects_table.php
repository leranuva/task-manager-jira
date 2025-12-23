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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('key')->unique(); // PROJ, DEV, etc. (como en Jira)
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6'); // Color hexadecimal
            $table->string('icon')->nullable(); // Icono del proyecto
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['team_id', 'is_active']);
            $table->index('owner_id');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
