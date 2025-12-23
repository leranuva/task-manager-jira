/**
 * useOptimisticUI Composable
 * Composable para Optimistic UI
 * 
 * A composable for implementing optimistic UI updates.
 * Updates the UI immediately before the server responds.
 * 
 * Un composable para implementar actualizaciones Optimistic UI.
 * Actualiza la UI inmediatamente antes de que el servidor responda.
 */

import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useNotificationStore } from '@/Stores/useNotificationStore';

/**
 * Use optimistic UI for task operations
 * Usar UI optimista para operaciones de tareas
 * 
 * @param {Function} updateFn - Function to update local state / Función para actualizar estado local
 * @param {Function} revertFn - Function to revert changes on error / Función para revertir cambios en error
 * @returns {Object} - Optimistic UI utilities / Utilidades de Optimistic UI
 */
export function useOptimisticUI(updateFn, revertFn) {
    const notificationStore = useNotificationStore();
    const isOptimistic = ref(false);
    const optimisticData = ref(null);

    /**
     * Execute optimistic update / Ejecutar actualización optimista
     * 
     * @param {string} url - Request URL / URL de solicitud
     * @param {Object} data - Data to update / Datos a actualizar
     * @param {Object} options - Request options / Opciones de solicitud
     */
    const execute = async (url, data, options = {}) => {
        // Store original data for revert / Almacenar datos originales para revertir
        optimisticData.value = { ...data };

        // Apply optimistic update / Aplicar actualización optimista
        isOptimistic.value = true;
        updateFn(data);

        try {
            // Make actual request using axios for API routes / Hacer solicitud real usando axios para rutas API
            // Get CSRF token from meta tag / Obtener token CSRF de meta tag
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            await axios.put(url, {
                ...data,
                ...options.data,
            }, {
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                withCredentials: true, // Enable cookies for Sanctum SPA / Habilitar cookies para Sanctum SPA
            });
            
            // Success / Éxito
            isOptimistic.value = false;
            optimisticData.value = null;
            if (options.successMessage) {
                notificationStore.success(options.successMessage);
            }
            
            // Call onSuccess callback if provided / Llamar callback onSuccess si se proporciona
            if (options.routerOptions?.onSuccess) {
                options.routerOptions.onSuccess();
            }
        } catch (error) {
            // Revert optimistic update / Revertir actualización optimista
            if (revertFn && optimisticData.value) {
                revertFn(optimisticData.value);
            }
            isOptimistic.value = false;
            optimisticData.value = null;
            
            // Show error notification / Mostrar notificación de error
            const errorMessage = options.errorMessage || 'Error al actualizar. / Error updating.';
            notificationStore.error(errorMessage);
            
            // Call onError callback if provided / Llamar callback onError si se proporciona
            if (options.routerOptions?.onError) {
                options.routerOptions.onError(error.response?.data?.errors || {});
            }
        }
    };

    /**
     * Move task optimistically / Mover tarea de forma optimista
     * 
     * @param {Object} task - Task to move / Tarea a mover
     * @param {string} newStatus - New status / Nuevo estado
     * @param {number} newPosition - New position / Nueva posición
     */
    const moveTask = async (task, newStatus, newPosition) => {
        // Ensure task has id / Asegurar que la tarea tenga id
        if (!task || !task.id) {
            throw new Error('Task must have an id');
        }

        // Store complete original task for revert / Almacenar tarea original completa para revertir
        const originalTask = JSON.parse(JSON.stringify(task)); // Deep clone / Clon profundo

        // Create updated task object / Crear objeto de tarea actualizado
        const updatedTask = {
            ...task,
            status: newStatus,
            position: newPosition,
        };

        // Store original data for revert / Almacenar datos originales para revertir
        optimisticData.value = originalTask;

        // Apply optimistic update / Aplicar actualización optimista
        isOptimistic.value = true;
        updateFn(updatedTask);

        try {
            // Use Inertia router for web routes (better for SPA auth) / Usar router de Inertia para rutas web (mejor para autenticación SPA)
            // Build URL manually to avoid route() issues / Construir URL manualmente para evitar problemas con route()
            const url = `/tasks/${task.id}`;
            
            await router.put(url, {
                status: newStatus,
                position: newPosition,
            }, {
                preserveState: true,
                preserveScroll: true,
                only: [], // Don't reload any props / No recargar ninguna prop
            });
            
            // Success / Éxito
            isOptimistic.value = false;
            optimisticData.value = null;
            notificationStore.success('Tarea movida exitosamente. / Task moved successfully.');
        } catch (error) {
            // Revert optimistic update / Revertir actualización optimista
            if (revertFn && optimisticData.value) {
                revertFn(optimisticData.value);
            }
            isOptimistic.value = false;
            optimisticData.value = null;
            
            // Show error notification / Mostrar notificación de error
            notificationStore.error('Error al mover la tarea. / Error moving task.');
            
            // Re-throw error so caller can handle it / Re-lanzar error para que el llamador pueda manejarlo
            throw error;
        }
    };

    return {
        isOptimistic,
        execute,
        moveTask,
    };
}
