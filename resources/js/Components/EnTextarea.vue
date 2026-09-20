<!--
Props:
- modelValue: current text value
- id: unique textarea identifier
- label: floating field label
- rows: visible textarea rows
- placeholder: optional input hint
- error: inline validation message
- disabled: disables interaction
- required: marks the field as required

Emits:
- update:modelValue: emitted when the text value changes

Slots:
- none
-->
<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        id: string;
        label: string;
        rows?: number;
        placeholder?: string;
        error?: string;
        disabled?: boolean;
        required?: boolean;
    }>(),
    {
        rows: 4,
        placeholder: '',
        error: undefined,
        disabled: false,
        required: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const describedBy = computed(() =>
    props.error ? `${props.id}-error` : undefined,
);
</script>

<template>
    <div class="space-y-1.5">
        <div class="relative">
            <textarea
                :id="id"
                :value="modelValue"
                :rows="rows"
                :placeholder="placeholder || ' '"
                :disabled="disabled"
                :required="required"
                :aria-invalid="Boolean(error)"
                :aria-describedby="describedBy"
                class="peer focus:border-primary-600 focus:ring-primary-100 w-full rounded-md border border-neutral-300 bg-white px-3 pt-5 pb-2 text-sm text-neutral-900 shadow-sm transition placeholder:text-transparent focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:bg-neutral-100 disabled:text-neutral-500"
                :class="{
                    'border-danger focus:border-danger focus:ring-red-100':
                        error,
                }"
                @input="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLTextAreaElement).value,
                    )
                "
            />
            <label
                :for="id"
                class="pointer-events-none absolute top-0 left-3 bg-white px-1 text-xs text-neutral-500"
                :class="{ 'text-danger': error }"
            >
                {{ label }}<span v-if="required" aria-hidden="true"> *</span>
            </label>
        </div>
        <p
            v-if="error"
            :id="`${id}-error`"
            class="text-danger text-sm"
            role="alert"
        >
            {{ error }}
        </p>
    </div>
</template>
