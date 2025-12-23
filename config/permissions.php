<?php

/**
 * Permissions Configuration
 * Configuración de Permisos
 * 
 * This file serves as the Single Source of Truth for all permissions in the system.
 * Este archivo sirve como la Fuente Única de Verdad para todos los permisos del sistema.
 * 
 * Structure: [name => [display_name, group, description]]
 * Estructura: [name => [display_name, group, description]]
 */

return [
    // Projects / Proyectos
    'project.create' => [
        'display_name' => 'Crear Proyecto',
        'group' => 'projects',
        'description' => 'Permite crear nuevos proyectos',
    ],
    'project.view' => [
        'display_name' => 'Ver Proyecto',
        'group' => 'projects',
        'description' => 'Permite ver proyectos',
    ],
    'project.update' => [
        'display_name' => 'Actualizar Proyecto',
        'group' => 'projects',
        'description' => 'Permite actualizar proyectos',
    ],
    'project.delete' => [
        'display_name' => 'Eliminar Proyecto',
        'group' => 'projects',
        'description' => 'Permite eliminar proyectos',
    ],

    // Tasks / Tareas
    'task.create' => [
        'display_name' => 'Crear Tarea',
        'group' => 'tasks',
        'description' => 'Permite crear nuevas tareas',
    ],
    'task.view' => [
        'display_name' => 'Ver Tarea',
        'group' => 'tasks',
        'description' => 'Permite ver tareas',
    ],
    'task.update' => [
        'display_name' => 'Actualizar Tarea',
        'group' => 'tasks',
        'description' => 'Permite actualizar tareas',
    ],
    'task.delete' => [
        'display_name' => 'Eliminar Tarea',
        'group' => 'tasks',
        'description' => 'Permite eliminar tareas',
    ],
    'task.assign' => [
        'display_name' => 'Asignar Tarea',
        'group' => 'tasks',
        'description' => 'Permite asignar tareas a usuarios',
    ],

    // Comments / Comentarios
    'comment.create' => [
        'display_name' => 'Crear Comentario',
        'group' => 'comments',
        'description' => 'Permite crear comentarios',
    ],
    'comment.view' => [
        'display_name' => 'Ver Comentario',
        'group' => 'comments',
        'description' => 'Permite ver comentarios',
    ],
    'comment.update' => [
        'display_name' => 'Actualizar Comentario',
        'group' => 'comments',
        'description' => 'Permite actualizar comentarios',
    ],
    'comment.delete' => [
        'display_name' => 'Eliminar Comentario',
        'group' => 'comments',
        'description' => 'Permite eliminar comentarios',
    ],

    // Teams / Equipos
    'team.manage' => [
        'display_name' => 'Gestionar Equipo',
        'group' => 'teams',
        'description' => 'Permite gestionar el equipo',
    ],
    'team.invite' => [
        'display_name' => 'Invitar Miembros',
        'group' => 'teams',
        'description' => 'Permite invitar miembros al equipo',
    ],
    'team.remove' => [
        'display_name' => 'Remover Miembros',
        'group' => 'teams',
        'description' => 'Permite remover miembros del equipo',
    ],
];

