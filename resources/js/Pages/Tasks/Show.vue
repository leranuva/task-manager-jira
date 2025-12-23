<script setup>
/**
 * Task Show Page
 * Página de detalle de tarea
 * 
 * Task detail page with comments, assignees, labels, and task information.
 * Página de detalle de tarea con comentarios, asignados, etiquetas e información de la tarea.
 */

import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import PriorityBadge from '@/Components/Base/PriorityBadge.vue';
import { useNotificationStore } from '@/Stores/useNotificationStore';
import CommentEditor from '@/Features/CommentEditor.vue';
import AssigneeSelector from '@/Features/AssigneeSelector.vue';

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
    comments: {
        type: Object,
        default: () => ({ data: [] }),
    },
    users: {
        type: Array,
        default: () => [],
    },
});


const notificationStore = useNotificationStore();

// Task status update form / Formulario de actualización de estado
const statusForm = useForm({
    status: props.task.status,
});

const updateStatus = () => {
    statusForm.put(route('tasks.update', props.task.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            notificationStore.success('Estado actualizado. / Status updated.');
        },
        onError: () => {
            notificationStore.error('Error al actualizar el estado. / Error updating status.');
        },
    });
};

// Delete task / Eliminar tarea
const deleteTask = () => {
    if (confirm('¿Estás seguro de eliminar esta tarea? / Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', props.task.id), {
            onSuccess: () => {
                notificationStore.success('Tarea eliminada exitosamente. / Task deleted successfully.');
                router.visit(route('tasks.index'));
            },
            onError: () => {
                notificationStore.error('Error al eliminar la tarea. / Error deleting task.');
            },
        });
    }
};

// Format dates / Formatear fechas
const formatDate = (date) => {
    if (!date) return null;
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout :title="`${task.key} - ${task.title}`" :projects="task.project ? [task.project] : []">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('projects.kanban', task.project_id)"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ task.key }}: {{ task.title }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ task.project?.name }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('tasks.edit', task.id)">
                        <BaseButton variant="secondary" size="sm">
                            Editar / Edit
                        </BaseButton>
                    </Link>
                    <BaseButton variant="danger" size="sm" @click="deleteTask">
                        Eliminar / Delete
                    </BaseButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content / Contenido Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Task Description / Descripción de Tarea -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Descripción / Description
                            </h3>
                            <div
                                v-if="task.description"
                                class="prose max-w-none text-gray-700"
                                v-html="task.description"
                            ></div>
                            <p v-else class="text-gray-500 italic">
                                Sin descripción / No description
                            </p>
                        </div>

                        <!-- Comments Section / Sección de Comentarios -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Comentarios / Comments ({{ comments.data?.length || 0 }})
                            </h3>
                            
                            <!-- Comment Editor / Editor de Comentarios -->
                            <CommentEditor
                                :commentable-type="'App\\\\Models\\\\Task'"
                                :commentable-id="task.id"
                                @comment-added="router.reload({ only: ['comments'] })"
                            />

                            <!-- Comments List / Lista de Comentarios -->
                            <div v-if="comments.data && comments.data.length > 0" class="mt-6 space-y-4">
                                <div
                                    v-for="comment in comments.data"
                                    :key="comment.id"
                                    class="border-b border-gray-200 pb-4 last:border-0 last:pb-0"
                                >
                                    <div class="flex items-start space-x-3">
                                        <img
                                            :src="comment.user?.profile_photo_url || `https://ui-avatars.com/api/?name=${comment.user?.name}`"
                                            :alt="comment.user?.name"
                                            class="w-10 h-10 rounded-full"
                                        />
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-gray-900">{{ comment.user?.name }}</span>
                                                <span class="text-sm text-gray-500">
                                                    {{ formatDate(comment.created_at) }}
                                                </span>
                                                <span v-if="comment.is_edited" class="text-xs text-gray-400">
                                                    (editado / edited)
                                                </span>
                                            </div>
                                            <p class="mt-2 text-gray-700">{{ comment.body }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="mt-6 text-center text-gray-500 py-8">
                                No hay comentarios aún. / No comments yet.
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar / Barra Lateral -->
                    <div class="space-y-6">
                        <!-- Task Details / Detalles de Tarea -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Detalles / Details
                            </h3>
                            
                            <div class="space-y-4">
                                <!-- Status / Estado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado / Status
                                    </label>
                                    <form @submit.prevent="updateStatus">
                                        <select
                                            v-model="statusForm.status"
                                            @change="updateStatus"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option value="todo">Por hacer / To Do</option>
                                            <option value="in_progress">En progreso / In Progress</option>
                                            <option value="in_review">En revisión / In Review</option>
                                            <option value="done">Completado / Done</option>
                                            <option value="cancelled">Cancelado / Cancelled</option>
                                        </select>
                                    </form>
                                </div>

                                <!-- Priority / Prioridad -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Prioridad / Priority
                                    </label>
                                    <PriorityBadge :priority="task.priority" />
                                </div>

                                <!-- Type / Tipo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo / Type
                                    </label>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        {{ task.type }}
                                    </span>
                                </div>

                                <!-- Story Points / Puntos de Historia -->
                                <div v-if="task.story_points">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Story Points
                                    </label>
                                    <span class="text-lg font-semibold text-gray-900">
                                        {{ task.story_points }}
                                    </span>
                                </div>

                                <!-- Due Date / Fecha de Vencimiento -->
                                <div v-if="task.due_date">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha de vencimiento / Due Date
                                    </label>
                                    <span class="text-gray-900">
                                        {{ formatDate(task.due_date) }}
                                    </span>
                                </div>

                                <!-- Created / Creado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Creado / Created
                                    </label>
                                    <span class="text-gray-900">
                                        {{ formatDate(task.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Assignees / Asignados -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Asignados / Assignees
                                </h3>
                                <AssigneeSelector
                                    :task="task"
                                    :users="users"
                                    @updated="router.reload({ only: ['task'] })"
                                />
                            </div>
                            
                            <div v-if="task.assignees && task.assignees.length > 0" class="space-y-2">
                                <div
                                    v-for="assignee in task.assignees"
                                    :key="assignee.id"
                                    class="flex items-center space-x-3"
                                >
                                    <img
                                        :src="assignee.profile_photo_url || `https://ui-avatars.com/api/?name=${assignee.name}`"
                                        :alt="assignee.name"
                                        class="w-8 h-8 rounded-full"
                                    />
                                    <span class="text-sm text-gray-900">{{ assignee.name }}</span>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-500">
                                Sin asignados / No assignees
                            </p>
                        </div>

                        <!-- Labels / Etiquetas -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                Etiquetas / Labels
                            </h3>
                            
                            <div v-if="task.labels && task.labels.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="label in task.labels"
                                    :key="label.id"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :style="{
                                        backgroundColor: label.color + '20',
                                        color: label.color,
                                        borderColor: label.color,
                                        borderWidth: '1px',
                                    }"
                                >
                                    {{ label.name }}
                                </span>
                            </div>
                            <p v-else class="text-sm text-gray-500">
                                Sin etiquetas / No labels
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

