<template>
    <div class="relative">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="flex items-center space-x-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            <div v-if="selectedProject" class="flex items-center space-x-2">
                <div 
                    class="w-3 h-3 rounded-full"
                    :style="{ backgroundColor: selectedProject.color }"
                ></div>
                <span class="font-medium text-gray-900">{{ selectedProject.name }}</span>
                <span class="text-sm text-gray-500">({{ selectedProject.key }})</span>
            </div>
            <div v-else class="text-gray-500">
                Seleccionar proyecto / Select project
            </div>
            <svg 
                class="w-4 h-4 text-gray-400 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown / Desplegable -->
        <div
            v-if="isOpen"
            v-click-outside="() => isOpen = false"
            class="absolute z-50 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto"
        >
            <!-- Search / Búsqueda -->
            <div class="p-3 border-b border-gray-200">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar proyecto... / Search project..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Projects List / Lista de Proyectos -->
            <div class="py-1">
                <button
                    v-for="project in filteredProjects"
                    :key="project.id"
                    @click="selectProject(project)"
                    class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none transition-colors"
                    :class="{ 'bg-blue-50': selectedProject?.id === project.id }"
                >
                    <div class="flex items-center space-x-3">
                        <div 
                            class="w-3 h-3 rounded-full flex-shrink-0"
                            :style="{ backgroundColor: project.color }"
                        ></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-900 truncate">{{ project.name }}</span>
                                <StatusBadge 
                                    v-if="!project.is_active" 
                                    status="inactive" 
                                    size="sm"
                                />
                            </div>
                            <div class="text-sm text-gray-500">{{ project.key }}</div>
                        </div>
                        <svg 
                            v-if="selectedProject?.id === project.id"
                            class="w-5 h-5 text-blue-600 flex-shrink-0"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </button>

                <div v-if="filteredProjects.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                    No se encontraron proyectos / No projects found
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
/**
 * ProjectSelector Component
 * Componente selector de proyectos
 * 
 * A project selector dropdown component with search functionality.
 * Un componente desplegable selector de proyectos con funcionalidad de búsqueda.
 */

import { ref, computed, onMounted } from 'vue';
import { useProjectStore } from '@/Stores/useProjectStore';
import StatusBadge from '@/Components/Base/StatusBadge.vue';

const props = defineProps({
    /**
     * Current selected project ID / ID del proyecto seleccionado actual
     */
    modelValue: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'selected']);

const projectStore = useProjectStore();
const isOpen = ref(false);
const searchQuery = ref('');

const selectedProject = computed(() => {
    if (!props.modelValue) return null;
    return projectStore.all.find(p => p.id === props.modelValue) || null;
});

const filteredProjects = computed(() => {
    let projects = projectStore.activeProjects;
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        projects = projects.filter(p => 
            p.name.toLowerCase().includes(query) ||
            p.key.toLowerCase().includes(query)
        );
    }
    
    return projects;
});

const selectProject = (project) => {
    emit('update:modelValue', project.id);
    emit('selected', project);
    projectStore.setCurrent(project);
    isOpen.value = false;
    searchQuery.value = '';
};

onMounted(() => {
    // Load projects if not loaded / Cargar proyectos si no están cargados
    if (!projectStore.hasProjects) {
        projectStore.load();
    }
    
    // Set current project if modelValue is set / Establecer proyecto actual si modelValue está establecido
    if (props.modelValue) {
        const project = projectStore.all.find(p => p.id === props.modelValue);
        if (project) {
            projectStore.setCurrent(project);
        }
    }
});

// Click outside directive / Directiva de clic fuera
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    },
};
</script>

