<!--
Props:
- as: semantic element used for the card root
- padded: applies the standard 24px content padding

Emits:
- none

Slots:
- default: card content
- header: optional card header
- footer: optional card footer
-->
<script setup lang="ts">
withDefaults(
    defineProps<{
        as?: 'article' | 'div' | 'section';
        padded?: boolean;
    }>(),
    {
        as: 'div',
        padded: true,
    },
);
</script>

<template>
    <component
        :is="as"
        class="rounded-lg border border-neutral-100 bg-white shadow-sm"
    >
        <div v-if="$slots.header" class="border-b border-neutral-100 px-6 py-4">
            <slot name="header" />
        </div>
        <div :class="{ 'p-6': padded }">
            <slot />
        </div>
        <div v-if="$slots.footer" class="border-t border-neutral-100 px-6 py-4">
            <slot name="footer" />
        </div>
    </component>
</template>
