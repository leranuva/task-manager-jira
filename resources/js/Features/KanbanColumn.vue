<template>
    <div
        class="flex-shrink-0 w-80 bg-gray-50 rounded-lg p-4 flex flex-col"
        :class="{ 'opacity-50': isDraggingOver }"
    >
        <!-- Column Header / Encabezado de Columna -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
                <StatusBadge :status="status" :size="'sm'" />
                <span class="text-sm font-medium text-gray-700">
                    {{ tasks.length }}
                </span>
            </div>
            <button
                v-if="showAddButton"
                @click="$emit('add-task', status)"
                class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-colors"
                title="Agregar tarea / Add task"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>

        <!-- Tasks Container / Contenedor de Tareas -->
        <div
            ref="dropZone"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
            @drop.prevent="handleDrop"
            class="flex-1 space-y-2 overflow-y-auto min-h-[200px] relative"
            :class="[
                isDraggingOver ? 'bg-blue-50 border-2 border-blue-300 border-dashed rounded drop-zone-active' : '',
                'transition-all duration-200'
            ]"
        >
            <TransitionGroup
                name="task"
                tag="div"
                class="space-y-2"
            >
                <TaskCard
                    v-for="task in sortedTasks"
                    :key="task.id || task.key || `task-${task.status}-${task.position}`"
                    :task="task"
                    :draggable="draggable"
                    @drag-start="handleDragStart"
                    @drag-end="handleDragEnd"
                    @click="$emit('task-clicked', task)"
                />
            </TransitionGroup>

            <!-- Empty State / Estado Vacío -->
            <div
                v-if="tasks.length === 0 && !isDraggingOver"
                class="flex items-center justify-center h-32 text-gray-400 text-sm"
            >
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p>Sin tareas / No tasks</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/**
 * KanbanColumn Component
 * Componente de columna Kanban
 * 
 * A Kanban column component that displays tasks in a specific status.
 * Supports drag and drop with optimistic UI updates.
 * 
 * Un componente de columna Kanban que muestra tareas en un estado específico.
 * Soporta drag and drop con actualizaciones Optimistic UI.
 */

import { ref, computed } from 'vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import TaskCard from '@/Components/Tasks/TaskCard.vue';
import { useDragStore } from '@/Stores/useDragStore';

const props = defineProps({
    /**
     * Column status / Estado de la columna
     * @type {'todo'|'in_progress'|'in_review'|'done'|'cancelled'}
     */
    status: {
        type: String,
        required: true,
    },
    
    /**
     * Tasks in this column / Tareas en esta columna
     * @type {Array}
     */
    tasks: {
        type: Array,
        default: () => [],
    },
    
    /**
     * Enable drag and drop / Habilitar drag and drop
     */
    draggable: {
        type: Boolean,
        default: true,
    },
    
    /**
     * Show add task button / Mostrar botón agregar tarea
     */
    showAddButton: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['task-moved', 'add-task', 'task-clicked']);

const dropZone = ref(null);
const isDraggingOver = ref(false);
const dragCounter = ref(0); // Counter to handle nested elements / Contador para manejar elementos anidados
const dragStore = useDragStore(); // Shared drag store / Store compartido de drag

// Sort tasks by position / Ordenar tareas por posición
const sortedTasks = computed(() => {
    return [...props.tasks].sort((a, b) => (a.position || 0) - (b.position || 0));
});

// Handle drag start / Manejar inicio de arrastre
const handleDragStart = (task, event) => {
    // Store task in shared store / Almacenar tarea en store compartido
    dragStore.setDraggedTask(task);
    
    // Set dataTransfer if available / Establecer dataTransfer si está disponible
    if (event && event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', task.id);
        event.dataTransfer.setData('application/json', JSON.stringify({ taskId: task.id, status: task.status }));
    }
};

// Handle drag end / Manejar fin de arrastre
const handleDragEnd = (event) => {
    // Reset state / Reiniciar estado
    isDraggingOver.value = false;
    dragCounter.value = 0;
    
    // Don't clear draggedTask here, let handleDrop do it / No limpiar draggedTask aquí, dejar que handleDrop lo haga
};

// Handle drag over / Manejar arrastre sobre
const handleDragOver = (event) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
    
    // Check if we have a dragged task and it's from a different status / Verificar si tenemos una tarea arrastrada y es de un estado diferente
    if (dragStore.draggedTask && dragStore.draggedTask.status !== props.status) {
        isDraggingOver.value = true;
    }
};

// Handle drag leave / Manejar salida de arrastre
const handleDragLeave = (event) => {
    dragCounter.value--;
    
    // Only hide if we've left the drop zone completely / Solo ocultar si hemos salido completamente de la zona de soltado
    if (dragCounter.value <= 0) {
        isDraggingOver.value = false;
        dragCounter.value = 0;
    }
};

// Handle drop / Manejar soltado
const handleDrop = (event) => {
    event.preventDefault();
    event.stopPropagation();
    isDraggingOver.value = false;
    dragCounter.value = 0;
    
    // Get task from shared store (set during dragstart) / Obtener tarea de store compartido (establecido durante dragstart)
    let task = dragStore.draggedTask;
    
    // If not in store, try to get from dataTransfer / Si no está en store, intentar obtener de dataTransfer
    if (!task && event.dataTransfer) {
        try {
            const jsonData = event.dataTransfer.getData('application/json');
            if (jsonData) {
                const data = JSON.parse(jsonData);
                task = { id: data.taskId, status: data.status };
            }
        } catch (e) {
            // Fallback to text data / Fallback a datos de texto
            const taskId = event.dataTransfer.getData('text/plain');
            if (taskId) {
                task = { id: taskId };
            }
        }
    }
    
    // Emit task moved event if task found and status is different / Emitir evento de tarea movida si se encuentra tarea y el estado es diferente
    if (task && task.id && task.status !== props.status) {
        emit('task-moved', {
            task: task,
            newStatus: props.status,
            newPosition: props.tasks.length,
        });
    }
    
    // Clear dragged task from store / Limpiar tarea arrastrada del store
    dragStore.clearDraggedTask();
};
</script>

<style scoped>
/* Task animations / Animaciones de tarea */
.task-enter-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.task-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: absolute;
    width: 100%;
}

.task-enter-from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
}

.task-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
}

.task-move {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Drag and drop styles / Estilos de drag and drop */
[draggable="true"] {
    cursor: grab;
}

[draggable="true"]:active {
    cursor: grabbing;
}

/* Drop zone animation / Animación de zona de soltado */
.drop-zone-active {
    animation: pulse-border 1.5s ease-in-out infinite;
}

@keyframes pulse-border {
    0%, 100% {
        border-color: rgb(59, 130, 246);
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
    }
    50% {
        border-color: rgb(37, 99, 235);
        box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
    }
}
</style>
