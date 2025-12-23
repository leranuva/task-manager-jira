<template>
    <div class="w-full">
        <label v-if="label" :for="inputId" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :id="inputId"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :class="inputClasses"
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"
        />
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
        <p v-if="hint && !error" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
    </div>
</template>

<script setup>
/**
 * BaseInput Component
 * Componente de input base reutilizable
 * 
 * A reusable input component with validation states.
 * Un componente de input reutilizable con estados de validación.
 */

import { computed } from 'vue';

const props = defineProps({
    /**
     * Input value / Valor del input
     */
    modelValue: {
        type: [String, Number],
        default: '',
    },
    
    /**
     * Input type / Tipo de input
     */
    type: {
        type: String,
        default: 'text',
    },
    
    /**
     * Input label / Etiqueta del input
     */
    label: {
        type: String,
        default: null,
    },
    
    /**
     * Input placeholder / Placeholder del input
     */
    placeholder: {
        type: String,
        default: null,
    },
    
    /**
     * Is required / Es requerido
     */
    required: {
        type: Boolean,
        default: false,
    },
    
    /**
     * Is disabled / Está deshabilitado
     */
    disabled: {
        type: Boolean,
        default: false,
    },
    
    /**
     * Error message / Mensaje de error
     */
    error: {
        type: String,
        default: null,
    },
    
    /**
     * Hint message / Mensaje de ayuda
     */
    hint: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const inputId = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`);

const inputClasses = computed(() => {
    const base = 'block w-full rounded-md border shadow-sm focus:ring-2 focus:ring-offset-0 transition-colors';
    const states = {
        default: 'border-gray-300 focus:border-blue-500 focus:ring-blue-500',
        error: 'border-red-300 focus:border-red-500 focus:ring-red-500',
        disabled: 'bg-gray-100 text-gray-500 cursor-not-allowed',
    };
    
    let state = states.default;
    if (props.error) state = states.error;
    if (props.disabled) state = states.disabled;
    
    return `${base} ${state}`;
});
</script>

