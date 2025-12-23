<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Pobla la base de datos de la aplicación.
     */
    public function run(): void
    {
        // Seed Roles and Permissions first / Poblar Roles y Permisos primero
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create test user with team / Crear usuario de prueba con equipo
        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Assign owner role to test user / Asignar rol de propietario al usuario de prueba
        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        if ($ownerRole && $user->currentTeam) {
            $user->roles()->attach($ownerRole->id, [
                'team_id' => $user->currentTeam->id,
            ]);
        }

        // Seed test data if in development / Poblar datos de prueba si está en desarrollo
        if (app()->environment('local')) {
            $this->call([
                TestDataSeeder::class,
            ]);
        }
    }
}
