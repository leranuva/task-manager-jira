<script setup>
/**
 * Dashboard Page
 * Página de Dashboard
 * 
 * Main dashboard showing recent projects, tasks, and statistics.
 * Dashboard principal mostrando proyectos recientes, tareas y estadísticas.
 */

import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import PriorityBadge from '@/Components/Base/PriorityBadge.vue';
import { useProjectStore } from '@/Stores/useProjectStore';

const props = defineProps({
    projects: {
        type: Object,
        default: () => ({ data: [] }),
    },
    recentTasks: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total_projects: 0,
            total_tasks: 0,
            active_tasks: 0,
            completed_tasks: 0,
        }),
    },
});

const projectStore = useProjectStore();

// Initialize store / Inicializar store
if (props.projects.data && props.projects.data.length > 0) {
    projectStore.setProjects(props.projects.data);
}

// Get recent projects / Obtener proyectos recientes
const recentProjects = computed(() => {
    return props.projects.data?.slice(0, 6) || [];
});
</script>

<template>
    <AppLayout title="Dashboard" :projects="projects.data">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">
                    Dashboard
                </h2>
                <Link :href="route('projects.create')">
                    <BaseButton variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Proyecto / New Project
                    </BaseButton>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Statistics Cards / Tarjetas de Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Projects / Total Proyectos -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Proyectos / Projects
                                </p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ stats.total_projects || 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tasks / Total Tareas -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Tareas Totales / Total Tasks
                                </p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ stats.total_tasks || 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active Tasks / Tareas Activas -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Tareas Activas / Active Tasks
                                </p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ stats.active_tasks || 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Tasks / Tareas Completadas -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Completadas / Completed
                                </p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ stats.completed_tasks || 0 }}
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Projects / Proyectos Recientes -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Proyectos Recientes / Recent Projects
                            </h3>
                            <Link
                                :href="route('projects.index')"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Ver todos / View all
                            </Link>
                        </div>

                        <div v-if="recentProjects.length > 0" class="space-y-3">
                            <Link
                                v-for="project in recentProjects"
                                :key="project.id"
                                :href="route('projects.kanban', project.id)"
                                class="block p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-4 h-4 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: project.color }"
                                        ></div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ project.name }}</h4>
                                            <p class="text-sm text-gray-500">{{ project.key }}</p>
                                        </div>
                                    </div>
                                    <StatusBadge
                                        :status="project.is_active ? 'active' : 'inactive'"
                                        size="sm"
                                    />
                                </div>
                                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                    <span>{{ project.tasks_count || 0 }} tareas</span>
                                    <span>{{ project.labels_count || 0 }} etiquetas</span>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <p>No hay proyectos aún. / No projects yet.</p>
                            <Link :href="route('projects.create')" class="mt-4 inline-block">
                                <BaseButton variant="primary" size="sm">
                                    Crear Proyecto / Create Project
                                </BaseButton>
                            </Link>
                        </div>
                    </div>

                    <!-- Recent Tasks / Tareas Recientes -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Tareas Recientes / Recent Tasks
                            </h3>
                            <Link
                                :href="route('tasks.index')"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Ver todas / View all
                            </Link>
                        </div>

                        <div v-if="recentTasks && recentTasks.length > 0" class="space-y-3">
                            <Link
                                v-for="task in recentTasks.slice(0, 5)"
                                :key="task.id"
                                :href="route('tasks.show', task.id)"
                                class="block p-4 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="text-xs font-medium text-gray-500">{{ task.key }}</span>
                                            <PriorityBadge :priority="task.priority" size="sm" />
                                        </div>
                                        <h4 class="font-medium text-gray-900 mb-1">{{ task.title }}</h4>
                                        <p class="text-sm text-gray-500">{{ task.project?.name }}</p>
                                    </div>
                                    <StatusBadge :status="task.status" size="sm" />
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <p>No hay tareas recientes. / No recent tasks.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
