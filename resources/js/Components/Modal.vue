<script setup>
import { computed, onUnmounted, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        e.preventDefault();
        close();
    }
};

watch(
    () => props.show,
    (value) => {
        document.body.style.overflow = value ? 'hidden' : null;
    }
);

onUnmounted(() => {
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => ({
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
}[props.maxWidth] || 'sm:max-w-2xl'));
</script>

<template>
    <div v-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60" @click="close"></div>

        <div class="relative w-full" :class="maxWidthClass">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                <slot />
            </div>
        </div>
    </div>
</template>
