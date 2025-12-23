<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="buttonClasses"
        @click="$emit('click', $event)"
    >
        <span v-if="loading" class="inline-block animate-spin mr-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </span>
        <slot />
    </button>
</template>

<script setup>
/**
 * BaseButton Component
 * Componente de botón base reutilizable
 * 
 * A reusable button component with variants and loading state.
 * Un componente de botón reutilizable con variantes y estado de carga.
 */

import { computed } from 'vue';

const props = defineProps({
    /**
     * Button variant / Variante del botón
     * @type {'primary'|'secondary'|'danger'|'success'|'outline'}
     */
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger', 'success', 'outline'].includes(value),
    },
    
    /**
     * Button size / Tamaño del botón
     * @type {'sm'|'md'|'lg'}
     */
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    
    /**
     * Button type / Tipo de botón
     */
    type: {
        type: String,
        default: 'button',
    },
    
    /**
     * Is disabled / Está deshabilitado
     */
    disabled: {
        type: Boolean,
        default: false,
    },
    
    /**
     * Is loading / Está cargando
     */
    loading: {
        type: Boolean,
        default: false,
    },
    
    /**
     * Full width / Ancho completo
     */
    fullWidth: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
    const base = 'inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    
    const variants = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 disabled:bg-blue-300',
        secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500 disabled:bg-gray-300',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-red-300',
        success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 disabled:bg-green-300',
        outline: 'border-2 border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500 disabled:border-gray-200 disabled:text-gray-400',
    };
    
    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-base',
        lg: 'px-6 py-3 text-lg',
    };
    
    const width = props.fullWidth ? 'w-full' : '';
    
    return `${base} ${variants[props.variant]} ${sizes[props.size]} ${width}`.trim();
});
</script>

