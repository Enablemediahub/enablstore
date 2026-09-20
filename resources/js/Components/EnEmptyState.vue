<!--
Props:
- title: empty-state heading
- description: supporting explanation

Emits:
- none

Slots:
- illustration: optional visual content
- action: optional recovery or creation action
-->
<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center px-6 py-12 text-center"
    >
        <div v-if="$slots.illustration" class="text-primary-600 mb-4">
            <slot name="illustration" />
        </div>
        <h2 class="text-lg font-semibold text-neutral-900">{{ title }}</h2>
        <p class="mt-2 max-w-md text-sm text-neutral-500">{{ description }}</p>
        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>
