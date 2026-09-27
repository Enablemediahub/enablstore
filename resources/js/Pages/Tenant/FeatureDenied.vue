<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, ShieldAlert } from '@lucide/vue';
import { onBeforeUnmount, onMounted } from 'vue';

const props = defineProps<{
    tenantName: string;
    featureName: string;
    dashboardUrl: string;
}>();

const returnToDashboard = (): void => router.visit(props.dashboardUrl, { replace: true });
const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') returnToDashboard();
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <Head title="Access denied" />
    <main class="fixed inset-0 z-50 flex min-h-screen items-center justify-center bg-neutral-950/60 p-4" @click.self="returnToDashboard">
        <section role="dialog" aria-modal="true" aria-labelledby="access-denied-title" class="w-full max-w-md rounded-lg bg-white p-6 text-neutral-900 shadow-2xl sm:p-8">
            <div class="flex items-start gap-4">
                <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-red-100 bg-white p-1.5">
                    <img src="/images/Enablstore.png" alt="Enablstore" class="h-full w-full object-contain" />
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-700">{{ tenantName }}</p>
                    <h1 id="access-denied-title" class="mt-1 text-xl font-black">Access denied</h1>
                </div>
                <ShieldAlert :size="22" class="ml-auto shrink-0 text-red-700" aria-hidden="true" />
            </div>
            <p class="mt-5 text-sm leading-6 text-neutral-600">
                {{ featureName }} isn’t included in your current subscription. Contact Enablstore to upgrade your plan and enable access.
            </p>
            <button type="button" class="mt-6 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-neutral-900 px-4 text-sm font-bold text-white transition hover:bg-neutral-700" @click="returnToDashboard">
                <ArrowLeft :size="16" aria-hidden="true" /> Return to workspace dashboard
            </button>
            <p class="mt-3 text-center text-xs text-neutral-500">Click outside this message or press Escape to return.</p>
        </section>
    </main>
</template>