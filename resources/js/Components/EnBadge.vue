<!--
Props:
- tone: semantic badge color
- label: optional accessible label when the badge is icon-only

Emits:
- none

Slots:
- default: badge content
-->
<script setup lang="ts">
import { computed } from 'vue';

type BadgeTone = 'neutral' | 'success' | 'warning' | 'danger' | 'info';

const props = withDefaults(
    defineProps<{
        tone?: BadgeTone;
        label?: string;
    }>(),
    {
        tone: 'neutral',
        label: undefined,
    },
);

const toneClasses = computed(() => ({
    'bg-neutral-100 text-neutral-700': props.tone === 'neutral',
    'bg-primary-100 text-primary-700': props.tone === 'success',
    'bg-accent-100 text-accent-700': props.tone === 'warning',
    'bg-red-100 text-red-700': props.tone === 'danger',
    'bg-blue-100 text-blue-700': props.tone === 'info',
}));
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="toneClasses"
        :aria-label="label"
    >
        <slot />
    </span>
</template>
