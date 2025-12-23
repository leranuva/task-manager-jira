<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permissions = [
            // Projects / Proyectos
            ['name' => 'project.create', 'display_name' => 'Crear Proyecto', 'group' => 'projects', 'description' => 'Permite crear nuevos proyectos'],
            ['name' => 'project.view', 'display_name' => 'Ver Proyecto', 'group' => 'projects', 'description' => 'Permite ver proyectos'],
            ['name' => 'project.update', 'display_name' => 'Actualizar Proyecto', 'group' => 'projects', 'description' => 'Permite actualizar proyectos'],
            ['name' => 'project.delete', 'display_name' => 'Eliminar Proyecto', 'group' => 'projects', 'description' => 'Permite eliminar proyectos'],
            
            // Tasks / Tareas
            ['name' => 'task.create', 'display_name' => 'Crear Tarea', 'group' => 'tasks', 'description' => 'Permite crear nuevas tareas'],
            ['name' => 'task.view', 'display_name' => 'Ver Tarea', 'group' => 'tasks', 'description' => 'Permite ver tareas'],
            ['name' => 'task.update', 'display_name' => 'Actualizar Tarea', 'group' => 'tasks', 'description' => 'Permite actualizar tareas'],
            ['name' => 'task.delete', 'display_name' => 'Eliminar Tarea', 'group' => 'tasks', 'description' => 'Permite eliminar tareas'],
            ['name' => 'task.assign', 'display_name' => 'Asignar Tarea', 'group' => 'tasks', 'description' => 'Permite asignar tareas a usuarios'],
            
            // Comments / Comentarios
            ['name' => 'comment.create', 'display_name' => 'Crear Comentario', 'group' => 'comments', 'description' => 'Permite crear comentarios'],
            ['name' => 'comment.view', 'display_name' => 'Ver Comentario', 'group' => 'comments', 'description' => 'Permite ver comentarios'],
            ['name' => 'comment.update', 'display_name' => 'Actualizar Comentario', 'group' => 'comments', 'description' => 'Permite actualizar comentarios'],
            ['name' => 'comment.delete', 'display_name' => 'Eliminar Comentario', 'group' => 'comments', 'description' => 'Permite eliminar comentarios'],
            
            // Teams / Equipos
            ['name' => 'team.manage', 'display_name' => 'Gestionar Equipo', 'group' => 'teams', 'description' => 'Permite gestionar el equipo'],
            ['name' => 'team.invite', 'display_name' => 'Invitar Miembros', 'group' => 'teams', 'description' => 'Permite invitar miembros al equipo'],
            ['name' => 'team.remove', 'display_name' => 'Remover Miembros', 'group' => 'teams', 'description' => 'Permite remover miembros del equipo'],
        ];

        $permission = fake()->randomElement($permissions);

        return [
            'name' => $permission['name'],
            'display_name' => $permission['display_name'],
            'group' => $permission['group'],
            'description' => $permission['description'],
        ];
    }
}
