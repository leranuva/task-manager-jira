/**
 * Drag Store
 * Store para Drag and Drop
 * 
 * Pinia store for managing drag and drop state across components.
 * Store de Pinia para gestionar el estado de drag and drop entre componentes.
 */

import { defineStore } from 'pinia';

export const useDragStore = defineStore('drag', {
    state: () => ({
        /**
         * Currently dragged task / Tarea actualmente arrastrada
         * @type {Object|null}
         */
        draggedTask: null,
        
        /**
         * Is dragging / Está arrastrando
         * @type {Boolean}
         */
        isDragging: false,
    }),
    
    actions: {
        /**
         * Set dragged task / Establecer tarea arrastrada
         * 
         * @param {Object} task - Task being dragged / Tarea siendo arrastrada
         */
        setDraggedTask(task) {
            this.draggedTask = task;
            this.isDragging = true;
        },
        
        /**
         * Clear dragged task / Limpiar tarea arrastrada
         */
        clearDraggedTask() {
            this.draggedTask = null;
            this.isDragging = false;
        },
    },
});

