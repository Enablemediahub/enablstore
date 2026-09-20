<!--
Props:
- variant: text, card, table, or chart placeholder
- lines: number of text skeleton lines

Emits:
- none

Slots:
- none
-->
<script setup lang="ts">
import { computed } from 'vue';

type SkeletonVariant = 'text' | 'card' | 'table' | 'chart';

const props = withDefaults(
    defineProps<{
        variant?: SkeletonVariant;
        lines?: number;
    }>(),
    {
        variant: 'text',
        lines: 3,
    },
);

const lineCount = computed(() => Math.max(1, Math.min(props.lines, 8)));
</script>

<template>
    <div v-if="variant === 'text'" class="space-y-2" aria-hidden="true">
        <div
            v-for="line in lineCount"
            :key="line"
            class="h-4 animate-pulse rounded bg-neutral-100"
            :class="{ 'w-3/4': line === lineCount }"
        />
    </div>
    <div
        v-else-if="variant === 'card'"
        class="h-40 animate-pulse rounded-lg bg-neutral-100"
        aria-hidden="true"
    />
    <div
        v-else-if="variant === 'chart'"
        class="h-64 animate-pulse rounded-lg bg-neutral-100"
        aria-hidden="true"
    />
    <div v-else class="space-y-3" aria-hidden="true">
        <div v-for="row in 5" :key="row" class="flex gap-4">
            <div class="h-4 flex-1 animate-pulse rounded bg-neutral-100" />
            <div class="h-4 w-1/4 animate-pulse rounded bg-neutral-100" />
        </div>
    </div>
</template>
