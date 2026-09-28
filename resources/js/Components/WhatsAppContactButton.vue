<script setup lang="ts">
import { MessageCircle } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    phone: string | null;
    message: string;
}>();

const whatsappUrl = computed(() => {
    const digits = (props.phone ?? '').replace(/\D/g, '');
    return digits.length >= 8 ? `https://wa.me/${digits}?text=${encodeURIComponent(props.message)}` : null;
});
</script>

<template>
    <a
        v-if="whatsappUrl"
        :href="whatsappUrl"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Chat with this store on WhatsApp"
        title="Chat with this store on WhatsApp"
        class="fixed right-5 bottom-5 z-50 grid size-14 place-items-center rounded-full bg-[#25D366] text-white shadow-[0_8px_24px_rgba(0,0,0,0.24)] transition hover:scale-105 hover:bg-[#1fb95a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#128C7E]"
    >
        <MessageCircle :size="27" aria-hidden="true" />
    </a>
</template>