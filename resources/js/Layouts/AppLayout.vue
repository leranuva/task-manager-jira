<script setup>
/**
 * AppLayout Component
 * Layout principal de la aplicación
 * 
 * Main application layout with sidebar, project selector, and user menu.
 * Layout principal de la aplicación con sidebar, selector de proyectos y menú de usuario.
 */

import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useSidebarStore } from '@/Stores/useSidebarStore';
import { useProjectStore } from '@/Stores/useProjectStore';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ProjectSelector from '@/Components/Projects/ProjectSelector.vue';
import NotificationToast from '@/Components/Base/NotificationToast.vue';

const props = defineProps({
    title: String,
    projects: {
        type: Array,
        default: () => [],
    },
});

const sidebarStore = useSidebarStore();
const projectStore = useProjectStore();

const showingNavigationDropdown = ref(false);

// Initialize stores / Inicializar stores
onMounted(() => {
    // Set projects if provided / Establecer proyectos si se proporcionan
    if (props.projects && props.projects.length > 0) {
        projectStore.setProjects(props.projects);
    }
    
    // Check mobile / Verificar móvil
    const checkMobile = () => {
        sidebarStore.setMobile(window.innerWidth < 768);
        if (window.innerWidth < 768) {
            sidebarStore.close();
        }
    };
    
    checkMobile();
    window.addEventListener('resize', checkMobile);
    
    onUnmounted(() => {
        window.removeEventListener('resize', checkMobile);
    });
});

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const toggleSidebar = () => {
    sidebarStore.toggle();
};

// Project selector handlers / Manejadores del selector de proyectos
const selectedProjectId = computed({
    get: () => projectStore.currentProject?.id || null,
    set: (value) => {
        if (value) {
            const project = projectStore.all.find(p => p.id === value);
            if (project) {
                projectStore.setCurrent(project);
            }
        }
    },
});

const handleProjectSelected = (project) => {
    projectStore.setCurrent(project);
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100 flex">
            <!-- Sidebar / Barra lateral -->
            <aside
                :class="[
                    'bg-white border-r border-gray-200 transition-all duration-300 ease-in-out',
                    sidebarStore.isOpen ? 'w-64' : 'w-0 md:w-16',
                    sidebarStore.isMobile && !sidebarStore.isOpen ? 'hidden' : 'block'
                ]"
            >
                <div class="h-full flex flex-col">
                    <!-- Logo / Logo -->
                    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200">
                        <Link 
                            v-if="sidebarStore.isOpen || sidebarStore.isMobile"
                            :href="route('dashboard')"
                            class="flex items-center space-x-2"
                        >
                            <ApplicationMark class="h-8 w-auto" />
                            <span v-if="sidebarStore.isOpen" class="font-bold text-xl text-gray-900">
                                Task Manager
                            </span>
                        </Link>
                        <button
                            v-else
                            @click="toggleSidebar"
                            class="p-2 rounded-md hover:bg-gray-100"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- Navigation / Navegación -->
                    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                        <NavLink 
                            :href="route('dashboard')" 
                            :active="route().current('dashboard')"
                            class="flex items-center space-x-3"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span v-if="sidebarStore.isOpen || sidebarStore.isMobile">Dashboard</span>
                        </NavLink>

                        <NavLink 
                            :href="route('projects.index')" 
                            :active="route().current('projects.*')"
                            class="flex items-center space-x-3"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span v-if="sidebarStore.isOpen || sidebarStore.isMobile">Proyectos</span>
                        </NavLink>

                        <NavLink 
                            :href="route('tasks.index')" 
                            :active="route().current('tasks.*')"
                            class="flex items-center space-x-3"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span v-if="sidebarStore.isOpen || sidebarStore.isMobile">Tareas</span>
                        </NavLink>
                    </nav>
                </div>
            </aside>

            <!-- Main Content / Contenido Principal -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Navigation / Navegación Superior -->
                <nav class="bg-white border-b border-gray-200">
                    <div class="px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <!-- Left: Project Selector / Izquierda: Selector de Proyectos -->
                            <div class="flex items-center space-x-4">
                                <button
                                    v-if="sidebarStore.isMobile"
                                    @click="toggleSidebar"
                                    class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>
                                
                                <ProjectSelector 
                                    v-model="selectedProjectId"
                                    @selected="handleProjectSelected"
                                />
                            </div>

                            <!-- Right: User Menu / Derecha: Menú de Usuario -->
                            <div class="flex items-center space-x-4">
                                <!-- Teams Dropdown / Desplegable de Equipos -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team.name }}
                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>
                                            <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                                Team Settings
                                            </DropdownLink>
                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-200" />
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>
                                                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>

                                <!-- User Dropdown / Desplegable de Usuario -->
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>
                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>
                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>
                                        <div class="border-t border-gray-200" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Heading / Encabezado de Página -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content / Contenido de Página -->
                <main class="flex-1 overflow-y-auto">
                    <slot />
                </main>
            </div>
        </div>

        <!-- Notifications / Notificaciones -->
        <NotificationToast />
    </div>
</template>
