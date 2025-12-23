<template>
    <span :class="badgeClasses">
        <slot>{{ label }}</slot>
    </span>
</template>

<script setup>
/**
 * PriorityBadge Component
 * Componente de badge de prioridad reutilizable
 * 
 * A reusable priority badge component for tasks.
 * Un componente de badge de prioridad reutilizable para tareas.
 */

import { computed } from 'vue';

const props = defineProps({
    /**
     * Priority level / Nivel de prioridad
     * @type {'lowest'|'low'|'medium'|'high'|'highest'}
     */
    priority: {
        type: String,
        default: 'medium',
        validator: (value) => !value || ['lowest', 'low', 'medium', 'high', 'highest'].includes(value),
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

const priorityConfig = {
    lowest: { 
        label: 'Muy baja / Lowest', 
        class: 'bg-gray-100 text-gray-600 border-gray-300',
        icon: '↓',
    },
    low: { 
        label: 'Baja / Low', 
        class: 'bg-blue-100 text-blue-600 border-blue-300',
        icon: '↓',
    },
    medium: { 
        label: 'Media / Medium', 
        class: 'bg-yellow-100 text-yellow-600 border-yellow-300',
        icon: '→',
    },
    high: { 
        label: 'Alta / High', 
        class: 'bg-orange-100 text-orange-600 border-orange-300',
        icon: '↑',
    },
    highest: { 
        label: 'Muy alta / Highest', 
        class: 'bg-red-100 text-red-600 border-red-300',
        icon: '↑↑',
    },
};

const badgeClasses = computed(() => {
    const base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border';
    const sizeClasses = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-0.5 text-xs',
        lg: 'px-3 py-1 text-sm',
    };
    
    const priority = props.priority || 'medium';
    const config = priorityConfig[priority] || priorityConfig.medium;
    
    return `${base} ${sizeClasses[props.size]} ${config.class}`;
});

const label = computed(() => {
    const priority = props.priority || 'medium';
    return props.label || priorityConfig[priority]?.label || priority;
});
</script>

