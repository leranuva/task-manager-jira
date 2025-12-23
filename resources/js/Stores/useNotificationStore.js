/**
 * Notification Store
 * Store de Pinia para gestionar notificaciones globales
 * 
 * This store manages global notifications that persist across page navigation.
 * Este store gestiona notificaciones globales que persisten entre navegaciones de página.
 */

import { defineStore } from 'pinia';

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        /**
         * Array of notifications / Array de notificaciones
         * @type {Array<{id: string, type: 'success'|'error'|'warning'|'info', message: string, duration?: number}>}
         */
        notifications: [],
    }),

    getters: {
        /**
         * Get all notifications / Obtener todas las notificaciones
         */
        all: (state) => state.notifications,

        /**
         * Get unread notifications count / Obtener conteo de notificaciones no leídas
         */
        unreadCount: (state) => state.notifications.length,
    },

    actions: {
        /**
         * Add a notification / Agregar una notificación
         * 
         * @param {string} message - Notification message / Mensaje de notificación
         * @param {string} type - Notification type / Tipo de notificación
         * @param {number} duration - Auto-dismiss duration in ms / Duración de auto-cierre en ms
         */
        add(message, type = 'info', duration = 5000) {
            const id = Date.now().toString() + Math.random().toString(36).substr(2, 9);
            
            const notification = {
                id,
                message,
                type,
                duration,
            };

            this.notifications.push(notification);

            // Auto-dismiss if duration is set / Auto-cerrar si se establece duración
            if (duration > 0) {
                setTimeout(() => {
                    this.remove(id);
                }, duration);
            }

            return id;
        },

        /**
         * Add success notification / Agregar notificación de éxito
         */
        success(message, duration = 5000) {
            return this.add(message, 'success', duration);
        },

        /**
         * Add error notification / Agregar notificación de error
         */
        error(message, duration = 7000) {
            return this.add(message, 'error', duration);
        },

        /**
         * Add warning notification / Agregar notificación de advertencia
         */
        warning(message, duration = 6000) {
            return this.add(message, 'warning', duration);
        },

        /**
         * Add info notification / Agregar notificación de información
         */
        info(message, duration = 5000) {
            return this.add(message, 'info', duration);
        },

        /**
         * Remove a notification / Eliminar una notificación
         * 
         * @param {string} id - Notification ID / ID de notificación
         */
        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        },

        /**
         * Clear all notifications / Limpiar todas las notificaciones
         */
        clear() {
            this.notifications = [];
        },
    },
});

