<script setup>
/**
 * Projects Index Page
 * Página de lista de proyectos
 * 
 * Projects listing page with search, filters, and project cards.
 * Página de listado de proyectos con búsqueda, filtros y tarjetas de proyectos.
 */

import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import StatusBadge from '@/Components/Base/StatusBadge.vue';
import { useProjectStore } from '@/Stores/useProjectStore';
import { useNotificationStore } from '@/Stores/useNotificationStore';

const props = defineProps({
    projects: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const projectStore = useProjectStore();
const notificationStore = useNotificationStore();

// Initialize store / Inicializar store
if (props.projects.data && props.projects.data.length > 0) {
    projectStore.setProjects(props.projects.data);
}

// Search and filters / Búsqueda y filtros
const searchForm = useForm({
    search: props.filters.search || '',
    active: props.filters.active !== undefined ? props.filters.active : null,
});

const search = () => {
    searchForm.get(route('projects.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.reset();
    searchForm.get(route('projects.index'));
};

// Delete project / Eliminar proyecto
const deleteProject = (project) => {
    if (confirm('¿Estás seguro de eliminar este proyecto? / Are you sure you want to delete this project?')) {
        router.delete(route('projects.destroy', project.id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                notificationStore.success('Proyecto eliminado exitosamente. / Project deleted successfully.');
                projectStore.remove(project.id);
            },
            onError: () => {
                notificationStore.error('Error al eliminar el proyecto. / Error deleting project.');
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="'Proyectos / Projects'" :projects="projects.data">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">
                    Proyectos / Projects
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
                <!-- Filters / Filtros -->
                <div class="mb-6 bg-white rounded-lg shadow p-4">
                    <form @submit.prevent="search" class="flex flex-wrap gap-4">
                        <!-- Search / Búsqueda -->
                        <div class="flex-1 min-w-[200px]">
                            <input
                                v-model="searchForm.search"
                                type="text"
                                placeholder="Buscar proyectos... / Search projects..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Active Filter / Filtro Activo -->
                        <div>
                            <select
                                v-model="searchForm.active"
                                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option :value="null">Todos / All</option>
                                <option :value="true">Activos / Active</option>
                                <option :value="false">Inactivos / Inactive</option>
                            </select>
                        </div>

                        <!-- Buttons / Botones -->
                        <div class="flex gap-2">
                            <BaseButton type="submit" variant="primary" :loading="searchForm.processing">
                                Buscar / Search
                            </BaseButton>
                            <BaseButton type="button" variant="outline" @click="clearFilters">
                                Limpiar / Clear
                            </BaseButton>
                        </div>
                    </form>
                </div>

                <!-- Projects Grid / Grid de Proyectos -->
                <div v-if="projects.data && projects.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="project in projects.data"
                        :key="project.id"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
                    >
                        <!-- Project Header / Encabezado del Proyecto -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-4 h-4 rounded-full flex-shrink-0"
                                    :style="{ backgroundColor: project.color }"
                                ></div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ project.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500">{{ project.key }}</p>
                                </div>
                            </div>
                            <StatusBadge 
                                :status="project.is_active ? 'active' : 'inactive'" 
                                size="sm"
                            />
                        </div>

                        <!-- Project Description / Descripción del Proyecto -->
                        <p v-if="project.description" class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ project.description }}
                        </p>

                        <!-- Project Stats / Estadísticas del Proyecto -->
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    {{ project.tasks_count || 0 }} tareas
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ project.labels_count || 0 }} etiquetas
                                </span>
                            </div>
                        </div>

                        <!-- Project Actions / Acciones del Proyecto -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <Link
                                :href="route('projects.kanban', project.id)"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                            >
                                Ver Kanban / View Kanban
                            </Link>
                            <div class="flex items-center space-x-2">
                                <Link
                                    :href="route('projects.edit', project.id)"
                                    class="text-sm text-gray-600 hover:text-gray-800"
                                >
                                    Editar / Edit
                                </Link>
                                <button
                                    @click="deleteProject(project)"
                                    class="text-sm text-red-600 hover:text-red-800"
                                >
                                    Eliminar / Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State / Estado Vacío -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay proyectos / No projects</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza creando tu primer proyecto. / Start by creating your first project.</p>
                    <div class="mt-6">
                        <Link :href="route('projects.create')">
                            <BaseButton variant="primary">
                                Nuevo Proyecto / New Project
                            </BaseButton>
                        </Link>
                    </div>
                </div>

                <!-- Pagination / Paginación -->
                <div v-if="projects.links && projects.links.length > 3" class="mt-6 flex justify-center">
                    <nav class="flex space-x-2">
                        <Link
                            v-for="link in projects.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-4 py-2 rounded-md text-sm font-medium',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                                !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

