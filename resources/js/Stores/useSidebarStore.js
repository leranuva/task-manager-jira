/**
 * Sidebar Store
 * Store de Pinia para gestionar el estado del sidebar
 * 
 * This store manages the sidebar state (open/closed) that persists across page navigation.
 * Este store gestiona el estado del sidebar (abierto/cerrado) que persiste entre navegaciones de página.
 */

import { defineStore } from 'pinia';

export const useSidebarStore = defineStore('sidebar', {
    state: () => ({
        /**
         * Sidebar is open / Sidebar está abierto
         * @type {boolean}
         */
        isOpen: true,

        /**
         * Sidebar is mobile / Sidebar es móvil
         * @type {boolean}
         */
        isMobile: false,
    }),

    // Getters removed - access state directly / Getters eliminados - acceder al state directamente
    // Use sidebarStore.isOpen and sidebarStore.isMobile directly / Usar sidebarStore.isOpen y sidebarStore.isMobile directamente

    actions: {
        /**
         * Toggle sidebar / Alternar sidebar
         */
        toggle() {
            this.isOpen = !this.isOpen;
        },

        /**
         * Open sidebar / Abrir sidebar
         */
        open() {
            this.isOpen = true;
        },

        /**
         * Close sidebar / Cerrar sidebar
         */
        close() {
            this.isOpen = false;
        },

        /**
         * Set mobile state / Establecer estado móvil
         * 
         * @param {boolean} mobile - Is mobile / Es móvil
         */
        setMobile(mobile) {
            this.isMobile = mobile;
        },
    },
});

