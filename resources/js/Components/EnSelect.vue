<!--
Props:
- modelValue: selected option value
- id: unique select identifier
- label: floating field label
- options: selectable values
- placeholder: empty option label
- error: inline validation message
- disabled: disables interaction
- required: marks the field as required

Emits:
- update:modelValue: emitted when the selected value changes

Slots:
- none
-->
<script setup lang="ts">
import { computed } from 'vue';

type SelectOption = {
    label: string;
    value: string;
};

const props = withDefaults(
    defineProps<{
        modelValue: string;
        id: string;
        label: string;
        options: SelectOption[];
        placeholder?: string;
        error?: string;
        disabled?: boolean;
        required?: boolean;
    }>(),
    {
        placeholder: 'Select an option',
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
            <select
                :id="id"
                :value="modelValue"
                :disabled="disabled"
                :required="required"
                :aria-invalid="Boolean(error)"
                :aria-describedby="describedBy"
                class="peer focus:border-primary-600 focus:ring-primary-100 min-h-11 w-full appearance-none rounded-md border border-neutral-300 bg-white px-3 pt-4 pb-1.5 text-sm text-neutral-900 shadow-sm transition focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:bg-neutral-100 disabled:text-neutral-500"
                :class="{
                    'border-danger focus:border-danger focus:ring-red-100':
                        error,
                }"
                @change="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLSelectElement).value,
                    )
                "
            >
                <option value="" disabled>{{ placeholder }}</option>
                <option
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
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
