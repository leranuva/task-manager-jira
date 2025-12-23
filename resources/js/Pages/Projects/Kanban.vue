<script setup>
/**
 * Kanban Page
 * Página de tablero Kanban
 * 
 * Kanban board page for managing tasks.
 * Página de tablero Kanban para gestionar tareas.
 */

import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import KanbanColumn from '@/Features/KanbanColumn.vue';
import { useOptimisticUI } from '@/Composables/useOptimisticUI';
import { useNotificationStore } from '@/Stores/useNotificationStore';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    tasks: {
        type: Array,
        default: () => [],
    },
});

const notificationStore = useNotificationStore();

// Normalize tasks to array / Normalizar tareas a array
const normalizeTasks = (tasks) => {
    if (!tasks) return [];
    if (Array.isArray(tasks)) return tasks;
    // If it's an object with data property (ResourceCollection) / Si es un objeto con propiedad data
    if (tasks.data && Array.isArray(tasks.data)) return tasks.data;
    // If it's an object, try to convert to array / Si es un objeto, intentar convertir a array
    return Object.values(tasks);
};

// Group tasks by status / Agrupar tareas por estado
const tasksByStatus = computed(() => {
    const grouped = {
        todo: [],
        in_progress: [],
        in_review: [],
        done: [],
        cancelled: [],
    };

    // Normalize tasks / Normalizar tareas
    const normalizedTasks = normalizeTasks(props.tasks);
    
    // Use localTasks for optimistic updates / Usar localTasks para actualizaciones optimistas
    const tasks = localTasks.value.length > 0 ? localTasks.value : normalizedTasks;
    
    if (Array.isArray(tasks)) {
        tasks.forEach(task => {
            if (task && grouped[task.status]) {
                grouped[task.status].push(task);
            }
        });
    }

    return grouped;
});

// Optimistic UI setup / Configuración de Optimistic UI
const localTasks = ref(normalizeTasks(props.tasks));

const updateTask = (updatedTask) => {
    // Ensure task has all required properties / Asegurar que la tarea tenga todas las propiedades requeridas
    if (!updatedTask.id) {
        console.error('Cannot update task without id:', updatedTask);
        return;
    }
    
    const index = localTasks.value.findIndex(t => t.id === updatedTask.id);
    if (index > -1) {
        // Preserve all existing properties and merge with updates / Preservar todas las propiedades existentes y fusionar con actualizaciones
        localTasks.value[index] = { 
            ...localTasks.value[index], 
            ...updatedTask,
            // Ensure required properties are present / Asegurar que las propiedades requeridas estén presentes
            id: updatedTask.id,
            key: localTasks.value[index].key || updatedTask.key,
            title: updatedTask.title || localTasks.value[index].title,
            priority: updatedTask.priority || localTasks.value[index].priority || 'medium',
        };
    } else {
        // If task doesn't exist, ensure it has all required properties / Si la tarea no existe, asegurar que tenga todas las propiedades requeridas
        localTasks.value.push({
            ...updatedTask,
            priority: updatedTask.priority || 'medium',
        });
    }
};

const revertTask = (originalTask) => {
    if (!originalTask || !originalTask.id) {
        console.error('Cannot revert task without id:', originalTask);
        return;
    }
    
    const index = localTasks.value.findIndex(t => t.id === originalTask.id);
    if (index > -1) {
        // Restore original task completely / Restaurar tarea original completamente
        localTasks.value[index] = { ...originalTask };
    }
};

const { moveTask } = useOptimisticUI(updateTask, revertTask);

// Sync localTasks with props.tasks / Sincronizar localTasks con props.tasks
watch(() => props.tasks, (newTasks) => {
    localTasks.value = normalizeTasks(newTasks);
}, { deep: true, immediate: true });

// Handle task moved / Manejar tarea movida
const handleTaskMoved = async ({ task, newStatus, newPosition }) => {
    // Don't move if status is the same / No mover si el estado es el mismo
    if (task.status === newStatus) {
        return;
    }
    
    // Find the full task object from localTasks / Encontrar el objeto completo de tarea desde localTasks
    const fullTask = localTasks.value.find(t => t.id === task.id) || task;
    
    if (!fullTask || !fullTask.id) {
        console.error('Task not found or invalid:', task);
        return;
    }
    
    // Optimistic update / Actualización optimista
    try {
        await moveTask(fullTask, newStatus, newPosition);
        
        // Reload tasks after move / Recargar tareas después de mover
        router.reload({
            only: ['tasks'],
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Error moving task:', error);
        notificationStore.error('Error al mover la tarea. / Error moving task.');
    }
};

// Handle task clicked / Manejar clic en tarea
const handleTaskClicked = (task) => {
    // Navigate to task detail / Navegar a detalle de tarea
    router.visit(route('tasks.show', task.id));
};
</script>

<template>
    <AppLayout :title="`Kanban - ${project?.name || 'Proyecto'}`" :projects="project ? [project] : []">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ project.name }} - Kanban
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Kanban Board / Tablero Kanban -->
                <div class="flex space-x-4 overflow-x-auto pb-4">
                    <KanbanColumn
                        status="todo"
                        :tasks="tasksByStatus.todo"
                        @task-moved="handleTaskMoved"
                        @task-clicked="handleTaskClicked"
                    />
                    
                    <KanbanColumn
                        status="in_progress"
                        :tasks="tasksByStatus.in_progress"
                        @task-moved="handleTaskMoved"
                        @task-clicked="handleTaskClicked"
                    />
                    
                    <KanbanColumn
                        status="in_review"
                        :tasks="tasksByStatus.in_review"
                        @task-moved="handleTaskMoved"
                        @task-clicked="handleTaskClicked"
                    />
                    
                    <KanbanColumn
                        status="done"
                        :tasks="tasksByStatus.done"
                        @task-moved="handleTaskMoved"
                        @task-clicked="handleTaskClicked"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

