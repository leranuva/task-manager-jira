<template>
    <div
        :draggable="draggable"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
        @click="$emit('click')"
        class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-all duration-200"
        :class="[
            isDragging ? 'opacity-50 scale-95 rotate-2' : 'hover:scale-[1.02]',
            'transform'
        ]"
    >
        <!-- Task Key and Title / Clave y Título de Tarea -->
        <div class="mb-2">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="text-xs font-medium text-gray-500">{{ task.key }}</span>
                        <PriorityBadge :priority="task.priority" size="sm" />
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 line-clamp-2">
                        {{ task.title }}
                    </h3>
                </div>
            </div>
        </div>

        <!-- Task Meta / Meta de Tarea -->
        <div class="flex items-center justify-between mt-3">
            <!-- Assignees / Asignados -->
            <div class="flex items-center space-x-1">
                <template v-if="Array.isArray(task.assignees) && task.assignees.length > 0">
                    <div
                        v-for="(assignee, index) in task.assignees.slice(0, 3)"
                        :key="assignee.id"
                        class="flex -space-x-2"
                        :style="{ zIndex: 10 - index }"
                    >
                        <img
                            :src="assignee.profile_photo_url || `https://ui-avatars.com/api/?name=${assignee.name}&background=random`"
                            :alt="assignee.name"
                            class="w-6 h-6 rounded-full border-2 border-white"
                            :title="assignee.name"
                        />
                    </div>
                    <span
                        v-if="task.assignees.length > 3"
                        class="text-xs text-gray-500 ml-1"
                    >
                        +{{ task.assignees.length - 3 }}
                    </span>
                </template>
                <span
                    v-else-if="task.assignees_count && task.assignees_count > 0"
                    class="text-xs text-gray-500"
                >
                    {{ task.assignees_count }} asignado(s)
                </span>
            </div>

            <!-- Story Points / Puntos de Historia -->
            <div v-if="task.story_points" class="flex items-center space-x-1 text-xs text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>{{ task.story_points }}</span>
            </div>
        </div>

        <!-- Labels / Etiquetas -->
        <div v-if="Array.isArray(task.labels) && task.labels.length > 0" class="flex flex-wrap gap-1 mt-2">
            <span
                v-for="label in task.labels.slice(0, 3)"
                :key="label.id"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                :style="{ 
                    backgroundColor: label.color + '20',
                    color: label.color,
                    borderColor: label.color
                }"
            >
                {{ label.name }}
            </span>
            <span
                v-if="task.labels.length > 3"
                class="text-xs text-gray-500"
            >
                +{{ task.labels.length - 3 }}
            </span>
        </div>
    </div>
</template>

<script setup>
/**
 * TaskCard Component
 * Componente de tarjeta de tarea
 * 
 * A task card component for displaying tasks in Kanban columns.
 * Supports drag and drop functionality.
 * 
 * Un componente de tarjeta de tarea para mostrar tareas en columnas Kanban.
 * Soporta funcionalidad de drag and drop.
 */

import { ref } from 'vue';
import PriorityBadge from '@/Components/Base/PriorityBadge.vue';

const props = defineProps({
    /**
     * Task object / Objeto de tarea
     */
    task: {
        type: Object,
        required: true,
    },
    
    /**
     * Enable drag and drop / Habilitar drag and drop
     */
    draggable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['drag-start', 'drag-end', 'click']);

const isDragging = ref(false);

const handleDragStart = (event) => {
    if (!props.draggable) {
        event.preventDefault();
        return;
    }
    
    isDragging.value = true;
    
    // Set dataTransfer / Establecer dataTransfer
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', props.task.id);
        event.dataTransfer.setData('application/json', JSON.stringify({ 
            taskId: props.task.id, 
            status: props.task.status 
        }));
    }
    
    // Emit custom event / Emitir evento personalizado
    emit('drag-start', props.task, event);
};

const handleDragEnd = (event) => {
    isDragging.value = false;
    emit('drag-end', event);
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

