<template>
    <span :class="badgeClasses">
        <slot>{{ label }}</slot>
    </span>
</template>

<script setup>
/**
 * StatusBadge Component
 * Componente de badge de estado reutilizable
 * 
 * A reusable status badge component for tasks, projects, etc.
 * Un componente de badge de estado reutilizable para tareas, proyectos, etc.
 */

import { computed } from 'vue';

const props = defineProps({
    /**
     * Status type / Tipo de estado
     * @type {'todo'|'in_progress'|'in_review'|'done'|'cancelled'|'active'|'inactive'}
     */
    status: {
        type: String,
        required: true,
    },
    
    /**
     * Custom label / Etiqueta personalizada
     */
    label: {
        type: String,
        default: null,
    },
    
    /**
     * Badge size / Tamaño del badge
     * @type {'sm'|'md'|'lg'}
     */
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
});

const statusConfig = {
    todo: { 
        label: 'Por hacer / To Do', 
        class: 'bg-gray-100 text-gray-800 border-gray-300' 
    },
    in_progress: { 
        label: 'En progreso / In Progress', 
        class: 'bg-blue-100 text-blue-800 border-blue-300' 
    },
    in_review: { 
        label: 'En revisión / In Review', 
        class: 'bg-yellow-100 text-yellow-800 border-yellow-300' 
    },
    done: { 
        label: 'Completado / Done', 
        class: 'bg-green-100 text-green-800 border-green-300' 
    },
    cancelled: { 
        label: 'Cancelado / Cancelled', 
        class: 'bg-red-100 text-red-800 border-red-300' 
    },
    active: { 
        label: 'Activo / Active', 
        class: 'bg-green-100 text-green-800 border-green-300' 
    },
    inactive: { 
        label: 'Inactivo / Inactive', 
        class: 'bg-gray-100 text-gray-800 border-gray-300' 
    },
};

const badgeClasses = computed(() => {
    const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border';
    const sizeClasses = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-0.5 text-xs',
        lg: 'px-3 py-1 text-sm',
    };
    
    const config = statusConfig[props.status] || statusConfig.todo;
    const label = props.label || config.label;
    
    return `${base} ${sizeClasses[props.size]} ${config.class}`;
});

const label = computed(() => {
    return props.label || statusConfig[props.status]?.label || props.status;
});
</script>

