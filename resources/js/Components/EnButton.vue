<!--
Props:
- variant: primary | accent | ghost | danger
- type: button | submit | reset
- disabled: disables interaction
- loading: shows the loading state and disables interaction
- ariaLabel: accessible label for icon-only buttons

Emits:
- click: emitted when the button is activated

Slots:
- default: button label
- leading: content before the label
- trailing: content after the label
-->
<script setup lang="ts">
import { computed } from 'vue';

type ButtonVariant = 'primary' | 'accent' | 'ghost' | 'danger';
type ButtonType = 'button' | 'submit' | 'reset';

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariant;
        type?: ButtonType;
        disabled?: boolean;
        loading?: boolean;
        ariaLabel?: string;
    }>(),
    {
        variant: 'primary',
        type: 'button',
        disabled: false,
        loading: false,
        ariaLabel: undefined,
    },
);

const emit = defineEmits<{
    click: [event: MouseEvent];
}>();

const classes = computed(() => [
    'inline-flex min-h-10 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition duration-150 ease-standard focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
    {
        'bg-primary-600 text-white shadow-sm hover:scale-[1.02] hover:bg-primary-700 hover:shadow-md active:scale-[0.98] focus-visible:outline-primary-600':
            props.variant === 'primary',
        'bg-accent-500 text-neutral-900 shadow-sm hover:scale-[1.02] hover:bg-accent-600 hover:shadow-md active:scale-[0.98] focus-visible:outline-accent-600':
            props.variant === 'accent',
        'border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50 active:bg-neutral-100 focus-visible:outline-primary-600':
            props.variant === 'ghost',
        'bg-danger text-white shadow-sm hover:scale-[1.02] hover:bg-red-600 hover:shadow-md active:scale-[0.98] focus-visible:outline-danger':
            props.variant === 'danger',
    },
]);

const handleClick = (event: MouseEvent): void => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>

<template>
    <button
        :type="type"
        :class="classes"
        :disabled="disabled || loading"
        :aria-label="ariaLabel"
        :aria-busy="loading"
        @click="handleClick"
    >
        <span
            v-if="loading"
            class="size-4 animate-pulse rounded-full bg-current/40"
            aria-hidden="true"
        />
        <span v-else class="inline-flex items-center" aria-hidden="true">
            <slot name="leading" />
        </span>
        <span><slot /></span>
        <span
            v-if="!loading"
            class="inline-flex items-center"
            aria-hidden="true"
        >
            <slot name="trailing" />
        </span>
    </button>
</template>
