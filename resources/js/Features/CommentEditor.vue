<template>
    <div class="space-y-4">
        <!-- Editor / Editor -->
        <div>
            <textarea
                v-model="commentForm.body"
                rows="4"
                :placeholder="placeholder"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-300': commentForm.errors.body }"
            ></textarea>
            <p v-if="commentForm.errors.body" class="mt-1 text-sm text-red-600">
                {{ commentForm.errors.body }}
            </p>
        </div>

        <!-- Actions / Acciones -->
        <div class="flex items-center justify-end space-x-2">
            <BaseButton
                v-if="commentForm.body"
                type="button"
                variant="outline"
                size="sm"
                @click="cancel"
            >
                Cancelar / Cancel
            </BaseButton>
            <BaseButton
                type="button"
                variant="primary"
                size="sm"
                :loading="commentForm.processing"
                :disabled="!commentForm.body || commentForm.processing"
                @click="submit"
            >
                Comentar / Comment
            </BaseButton>
        </div>
    </div>
</template>

<script setup>
/**
 * CommentEditor Component
 * Editor de comentarios
 * 
 * A comment editor component for adding comments to tasks, projects, etc.
 * Un componente editor de comentarios para agregar comentarios a tareas, proyectos, etc.
 */

import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useNotificationStore } from '@/Stores/useNotificationStore';
import BaseButton from '@/Components/Base/BaseButton.vue';

const props = defineProps({
    /**
     * Commentable type / Tipo comentable
     * @type {string}
     */
    commentableType: {
        type: String,
        required: true,
    },
    
    /**
     * Commentable ID / ID comentable
     * @type {string}
     */
    commentableId: {
        type: String,
        required: true,
    },
    
    /**
     * Parent comment ID for replies / ID de comentario padre para respuestas
     */
    parentId: {
        type: String,
        default: null,
    },
    
    /**
     * Placeholder text / Texto de placeholder
     */
    placeholder: {
        type: String,
        default: 'Escribe un comentario... / Write a comment...',
    },
});

const emit = defineEmits(['comment-added', 'cancelled']);

const notificationStore = useNotificationStore();

const commentForm = useForm({
    body: '',
    commentable_type: props.commentableType,
    commentable_id: props.commentableId,
    parent_id: props.parentId,
});

const submit = () => {
    commentForm.post(route('comments.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            notificationStore.success('Comentario agregado. / Comment added.');
            commentForm.reset();
            emit('comment-added');
        },
        onError: () => {
            notificationStore.error('Error al agregar el comentario. / Error adding comment.');
        },
    });
};

const cancel = () => {
    commentForm.reset();
    emit('cancelled');
};
</script>

