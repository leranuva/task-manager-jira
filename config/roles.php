<?php

/**
 * Roles Configuration
 * Configuración de Roles
 * 
 * This file serves as the Single Source of Truth for all roles in the system.
 * Este archivo sirve como la Fuente Única de Verdad para todos los roles del sistema.
 * 
 * Structure: [name => [display_name, description, permissions]]
 * Estructura: [name => [display_name, description, permissions]]
 */

return [
    'owner' => [
        'display_name' => 'Propietario',
        'description' => 'Propietario del equipo con todos los permisos',
        'permissions' => '*', // All permissions / Todos los permisos
    ],
    'admin' => [
        'display_name' => 'Administrador',
        'description' => 'Administrador con permisos de gestión',
        'permissions' => [
            // All project permissions / Todos los permisos de proyectos
            'project.create',
            'project.view',
            'project.update',
            'project.delete',
            // All task permissions / Todos los permisos de tareas
            'task.create',
            'task.view',
            'task.update',
            'task.delete',
            'task.assign',
            // All comment permissions / Todos los permisos de comentarios
            'comment.create',
            'comment.view',
            'comment.update',
            'comment.delete',
            // Team invite only / Solo invitar miembros
            'team.invite',
        ],
    ],
    'member' => [
        'display_name' => 'Miembro',
        'description' => 'Miembro del equipo con permisos básicos',
        'permissions' => [
            // View projects / Ver proyectos
            'project.view',
            'project.update',
            // Task permissions / Permisos de tareas
            'task.create',
            'task.view',
            'task.update',
            'task.assign',
            // Comment permissions / Permisos de comentarios
            'comment.create',
            'comment.view',
            'comment.update',
        ],
    ],
    'viewer' => [
        'display_name' => 'Visualizador',
        'description' => 'Solo puede ver, sin permisos de edición',
        'permissions' => [
            // View only / Solo visualización
            'project.view',
            'task.view',
            'comment.view',
        ],
    ],
];

