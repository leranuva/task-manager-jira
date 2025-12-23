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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name'); // owner, admin, member, viewer
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // Roles del sistema no se pueden eliminar
            $table->timestamps();
            
            $table->unique(['team_id', 'name']);
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
