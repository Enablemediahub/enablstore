<!--
Props:
- modelValue: current input value
- id: unique input identifier
- label: floating field label
- type: HTML input type
- placeholder: optional input hint
- error: inline validation message
- helpText: supporting field guidance
- disabled: disables interaction
- required: marks the field as required

Emits:
- update:modelValue: emitted when the input value changes

Slots:
- leading: content before the input
- trailing: content after the input
-->
<script setup lang="ts">
import { Eye, EyeOff } from '@lucide/vue';
import { computed, ref } from 'vue';

type InputType =
    'email' | 'number' | 'password' | 'search' | 'tel' | 'text' | 'url';

const props = withDefaults(
    defineProps<{
        modelValue: string | number;
        id: string;
        label: string;
        type?: InputType;
        placeholder?: string;
        error?: string;
        helpText?: string;
        disabled?: boolean;
        required?: boolean;
    }>(),
    {
        type: 'text',
        placeholder: '',
        error: undefined,
        helpText: undefined,
        disabled: false,
        required: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const revealed = ref(false);

const resolvedType = computed(() =>
    props.type === 'password' && revealed.value ? 'text' : props.type,
);

const describedBy = computed(() => {
    const ids: string[] = [];

    if (props.error) {
        ids.push(`${props.id}-error`);
    }

    if (props.helpText) {
        ids.push(`${props.id}-help`);
    }

    return ids.length > 0 ? ids.join(' ') : undefined;
});
</script>

<template>
    <div class="space-y-1.5">
        <div class="relative">
            <slot name="leading" />
            <input
                :id="id"
                :type="resolvedType"
                :value="modelValue"
                :placeholder="placeholder || ' '"
                :disabled="disabled"
                :required="required"
                :aria-invalid="Boolean(error)"
                :aria-describedby="describedBy"
                class="peer focus:border-primary-600 focus:ring-primary-100 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 pt-4 pb-1.5 text-sm font-medium text-neutral-900 shadow-sm transition placeholder:text-transparent hover:border-neutral-500 focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:bg-neutral-100 disabled:text-neutral-500"
                :class="{
                    'border-danger focus:border-danger focus:ring-red-100':
                        error,
                }"
                @input="
                    emit(
                        'update:modelValue',
                        ($event.target as HTMLInputElement).value,
                    )
                "
            />
            <label
                :for="id"
                class="peer-focus:text-primary-700 pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 bg-white px-1 text-sm font-medium text-neutral-700 transition-all peer-focus:top-0 peer-focus:text-xs peer-[:not(:placeholder-shown)]:top-0 peer-[:not(:placeholder-shown)]:text-xs"
                :class="{ 'text-danger peer-focus:text-danger': error }"
            >
                {{ label }}<span v-if="required" aria-hidden="true"> *</span>
            </label>
            <button
                v-if="type === 'password'"
                type="button"
                class="absolute top-1/2 right-3 -translate-y-1/2 text-neutral-500 hover:text-neutral-900"
                :aria-label="revealed ? 'Hide password' : 'Show password'"
                @click="revealed = !revealed"
            >
                <EyeOff v-if="revealed" :size="18" aria-hidden="true" />
                <Eye v-else :size="18" aria-hidden="true" />
            </button>
            <slot v-else name="trailing" />
        </div>
        <p
            v-if="error"
            :id="`${id}-error`"
            class="text-danger text-sm"
            role="alert"
        >
            {{ error }}
        </p>
        <p
            v-else-if="helpText"
            :id="`${id}-help`"
            class="text-sm text-neutral-500"
        >
            {{ helpText }}
        </p>
    </div>
</template>
