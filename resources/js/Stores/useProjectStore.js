/**
 * Project Store
 * Store de Pinia para gestionar proyectos activos
 * 
 * This store manages the current project and list of projects for quick access.
 * Este store gestiona el proyecto actual y la lista de proyectos para acceso rápido.
 */

import { defineStore } from 'pinia';
import { router } from '@inertiajs/vue3';

export const useProjectStore = defineStore('projects', {
    state: () => ({
        /**
         * Current active project / Proyecto activo actual
         * @type {Object|null}
         */
        currentProject: null,

        /**
         * List of projects / Lista de proyectos
         * @type {Array}
         */
        projects: [],

        /**
         * Projects are loading / Los proyectos se están cargando
         * @type {boolean}
         */
        isLoading: false,
    }),

    getters: {
        /**
         * Get current project / Obtener proyecto actual
         */
        active: (state) => state.currentProject,

        /**
         * Get all projects / Obtener todos los proyectos
         */
        all: (state) => state.projects,

        /**
         * Get active projects only / Obtener solo proyectos activos
         */
        activeProjects: (state) => state.projects.filter(p => p.is_active),

        /**
         * Check if has projects / Verificar si tiene proyectos
         */
        hasProjects: (state) => state.projects.length > 0,
    },

    actions: {
        /**
         * Set current project / Establecer proyecto actual
         * 
         * @param {Object} project - Project object / Objeto de proyecto
         */
        setCurrent(project) {
            this.currentProject = project;
        },

        /**
         * Set projects list / Establecer lista de proyectos
         * 
         * @param {Array} projects - Array of projects / Array de proyectos
         */
        setProjects(projects) {
            this.projects = projects;
        },

        /**
         * Add project to list / Agregar proyecto a la lista
         * 
         * @param {Object} project - Project object / Objeto de proyecto
         */
        add(project) {
            const exists = this.projects.find(p => p.id === project.id);
            if (!exists) {
                this.projects.push(project);
            }
        },

        /**
         * Update project in list / Actualizar proyecto en la lista
         * 
         * @param {string} id - Project ID / ID del proyecto
         * @param {Object} data - Project data / Datos del proyecto
         */
        update(id, data) {
            const index = this.projects.findIndex(p => p.id === id);
            if (index > -1) {
                this.projects[index] = { ...this.projects[index], ...data };
            }

            // Update current if it's the same / Actualizar actual si es el mismo
            if (this.currentProject?.id === id) {
                this.currentProject = { ...this.currentProject, ...data };
            }
        },

        /**
         * Remove project from list / Eliminar proyecto de la lista
         * 
         * @param {string} id - Project ID / ID del proyecto
         */
        remove(id) {
            this.projects = this.projects.filter(p => p.id !== id);
            
            // Clear current if it's the same / Limpiar actual si es el mismo
            if (this.currentProject?.id === id) {
                this.currentProject = null;
            }
        },

        /**
         * Load projects from server / Cargar proyectos del servidor
         */
        async load() {
            this.isLoading = true;
            
            try {
                router.reload({
                    only: ['projects'],
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (page) => {
                        if (page.props.projects) {
                            this.setProjects(page.props.projects);
                        }
                    },
                    onFinish: () => {
                        this.isLoading = false;
                    },
                });
            } catch (error) {
                this.isLoading = false;
                console.error('Error loading projects:', error);
            }
        },
    },
});

