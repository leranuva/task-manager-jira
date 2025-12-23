<script setup>
/**
 * Create Project Page
 * Página de crear proyecto
 * 
 * Form page for creating a new project with reactive form handling.
 * Página de formulario para crear un nuevo proyecto con manejo de formulario reactivo.
 */

import { computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BaseButton from '@/Components/Base/BaseButton.vue';
import BaseInput from '@/Components/Base/BaseInput.vue';
import { useNotificationStore } from '@/Stores/useNotificationStore';

const notificationStore = useNotificationStore();

const form = useForm({
    name: '',
    key: '',
    description: '',
    color: '#3B82F6',
    icon: '',
    is_active: true,
    settings: {
        default_task_type: 'task',
        default_priority: 'medium',
    },
});

// Auto-generate key from name / Auto-generar clave desde el nombre
const generateKey = () => {
    if (!form.key && form.name) {
        const key = form.name
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '')
            .substring(0, 10);
        form.key = key || 'PROJ';
    }
};

const submit = () => {
    form.post(route('projects.store'), {
        preserveScroll: true,
        onSuccess: () => {
            notificationStore.success('Proyecto creado exitosamente. / Project created successfully.');
            router.visit(route('projects.index'));
        },
        onError: () => {
            notificationStore.error('Error al crear el proyecto. / Error creating project.');
        },
    });
};
</script>

<template>
    <AppLayout title="Nuevo Proyecto / New Project">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">
                    Nuevo Proyecto / New Project
                </h2>
                <Link :href="route('projects.index')">
                    <BaseButton variant="outline">
                        Cancelar / Cancel
                    </BaseButton>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                    <!-- Name / Nombre -->
                    <BaseInput
                        v-model="form.name"
                        label="Nombre del Proyecto / Project Name"
                        placeholder="Mi Proyecto / My Project"
                        required
                        :error="form.errors.name"
                        @input="generateKey"
                    />

                    <!-- Key / Clave -->
                    <BaseInput
                        v-model="form.key"
                        label="Clave del Proyecto / Project Key"
                        placeholder="PROJ"
                        required
                        :error="form.errors.key"
                        hint="Máximo 10 caracteres, solo letras mayúsculas y números / Max 10 characters, uppercase letters and numbers only"
                    />

                    <!-- Description / Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción / Description
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-300': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Color / Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Color / Color
                        </label>
                        <div class="flex items-center space-x-4">
                            <input
                                v-model="form.color"
                                type="color"
                                class="h-10 w-20 rounded border border-gray-300"
                            />
                            <input
                                v-model="form.color"
                                type="text"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="#3B82F6"
                            />
                        </div>
                        <p v-if="form.errors.color" class="mt-1 text-sm text-red-600">
                            {{ form.errors.color }}
                        </p>
                    </div>

                    <!-- Icon / Icono -->
                    <BaseInput
                        v-model="form.icon"
                        label="Icono / Icon"
                        placeholder="briefcase, rocket, code..."
                        :error="form.errors.icon"
                        hint="Nombre del icono (opcional) / Icon name (optional)"
                    />

                    <!-- Active Status / Estado Activo -->
                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Proyecto activo / Active project
                        </label>
                    </div>

                    <!-- Settings / Configuración -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Configuración / Settings
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Default Task Type / Tipo de Tarea por Defecto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de tarea por defecto / Default Task Type
                                </label>
                                <select
                                    v-model="form.settings.default_task_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="task">Tarea / Task</option>
                                    <option value="bug">Bug</option>
                                    <option value="feature">Funcionalidad / Feature</option>
                                    <option value="epic">Épica / Epic</option>
                                    <option value="story">Historia / Story</option>
                                </select>
                            </div>

                            <!-- Default Priority / Prioridad por Defecto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Prioridad por defecto / Default Priority
                                </label>
                                <select
                                    v-model="form.settings.default_priority"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="lowest">Muy baja / Lowest</option>
                                    <option value="low">Baja / Low</option>
                                    <option value="medium">Media / Medium</option>
                                    <option value="high">Alta / High</option>
                                    <option value="highest">Muy alta / Highest</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions / Acciones del Formulario -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                        <Link :href="route('projects.index')">
                            <BaseButton type="button" variant="outline">
                                Cancelar / Cancel
                            </BaseButton>
                        </Link>
                        <BaseButton
                            type="submit"
                            variant="primary"
                            :loading="form.processing"
                            :disabled="form.processing"
                        >
                            Crear Proyecto / Create Project
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

