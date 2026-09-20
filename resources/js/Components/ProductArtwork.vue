<!--
Props:
- name: product name used for alternative text
- sku: product SKU used to select the local artwork
- imagePath: optional persisted product image path
- size: compact or large presentation

Emits:
- none

Slots:
- none
-->
<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name: string;
        sku: string;
        imagePath?: string | null;
        size?: 'compact' | 'large';
    }>(),
    {
        imagePath: null,
        size: 'compact',
    },
);

const source = computed(() => {
    if (props.imagePath) {
        const path = props.imagePath.replace(/^\//, '');

        if (path.startsWith('storage/')) {
            const tenant = String(route().params.tenant ?? '');

            return `/client/${tenant}/media/${path.replace(/^storage\//, '')}`;
        }

        return `/${path}`;
    }

    const sku = props.sku.toLowerCase();

    if (sku.startsWith('oil') || sku.startsWith('frytol')) {
        return '/images/products/cooking-oil.svg';
    }

    if (sku.startsWith('mil')) {
        return '/images/products/porridge.svg';
    }

    if (sku.startsWith('wat')) {
        return '/images/products/water.svg';
    }

    return '/images/products/catalogue.svg';
});
</script>

<template>
    <div
        class="overflow-hidden bg-neutral-50"
        :class="size === 'large' ? 'aspect-[4/3] rounded-3xl' : 'aspect-[5/4] rounded-2xl'"
    >
        <img
            :src="source"
            :alt="`${name} product image`"
            class="h-full w-full object-contain p-2 transition duration-300 group-hover:scale-105"
        />
    </div>
</template>
