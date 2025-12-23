<template>
    <div class="relative">
        <button
            type="button"
            @click="isOpen = !isOpen"
            class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            title="Asignar usuarios / Assign users"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>

        <!-- Dropdown / Desplegable -->
        <div
            v-if="isOpen"
            v-click-outside="() => isOpen = false"
            class="absolute z-50 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 max-h-96 overflow-y-auto"
            style="right: 0;"
        >
            <!-- Search / Búsqueda -->
            <div class="p-3 border-b border-gray-200">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar usuarios... / Search users..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Users List / Lista de Usuarios -->
            <div class="py-1">
                <label
                    v-for="user in filteredUsers"
                    :key="user.id"
                    class="flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer"
                >
                    <input
                        :checked="isAssigned(user.id)"
                        type="checkbox"
                        @change="toggleAssignee(user.id)"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <div class="ml-3 flex items-center space-x-2 flex-1">
                        <img
                            :src="user.profile_photo_url || `https://ui-avatars.com/api/?name=${user.name}`"
                            :alt="user.name"
                            class="w-8 h-8 rounded-full"
                        />
                        <span class="text-sm text-gray-900">{{ user.name }}</span>
                    </div>
                </label>

                <div v-if="filteredUsers.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                    No se encontraron usuarios / No users found
                </div>
            </div>

            <!-- Actions / Acciones -->
            <div class="p-3 border-t border-gray-200">
                <BaseButton
                    variant="primary"
                    size="sm"
                    full-width
                    :loading="assignForm.processing"
                    @click="saveAssignments"
                >
                    Guardar / Save
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<script setup>
/**
 * AssigneeSelector Component
 * Selector de asignados
 * 
 * A component for selecting and assigning users to tasks.
 * Un componente para seleccionar y asignar usuarios a tareas.
 */

import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useNotificationStore } from '@/Stores/useNotificationStore';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    /**
     * Task object / Objeto de tarea
     */
    task: {
        type: Object,
        required: true,
    },
    
    /**
     * Available users / Usuarios disponibles
     */
    users: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['updated']);

const notificationStore = useNotificationStore();
const isOpen = ref(false);
const searchQuery = ref('');
const selectedUserIds = ref([]);

// Initialize selected users / Inicializar usuarios seleccionados
if (props.task.assignees) {
    selectedUserIds.value = props.task.assignees.map(a => a.id);
}

const assignForm = useForm({
    user_ids: selectedUserIds.value,
});

// Filter users / Filtrar usuarios
const filteredUsers = computed(() => {
    let users = props.users;
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        users = users.filter(u => 
            u.name.toLowerCase().includes(query) ||
            u.email.toLowerCase().includes(query)
        );
    }
    
    return users;
});

// Check if user is assigned / Verificar si el usuario está asignado
const isAssigned = (userId) => {
    return selectedUserIds.value.includes(userId);
};

// Toggle assignee / Alternar asignado
const toggleAssignee = (userId) => {
    const index = selectedUserIds.value.indexOf(userId);
    if (index > -1) {
        selectedUserIds.value.splice(index, 1);
    } else {
        selectedUserIds.value.push(userId);
    }
};

// Save assignments / Guardar asignaciones
const saveAssignments = () => {
    assignForm.user_ids = selectedUserIds.value;
    
    assignForm.post(route('tasks.assign', props.task.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            notificationStore.success('Asignaciones actualizadas. / Assignments updated.');
            isOpen.value = false;
            emit('updated');
        },
        onError: () => {
            notificationStore.error('Error al actualizar las asignaciones. / Error updating assignments.');
        },
    });
};

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

