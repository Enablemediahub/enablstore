<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ catalogueMode: 'shared' | 'separate_online' }>();
const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
const form = useForm({ catalogue_mode: props.catalogueMode });
const submit = (): void => form.patch(route('tenant.settings.update', { tenant }));
</script>

<template>
    <Head title="Settings" />
    <main class="min-h-screen bg-neutral-50"><div class="mx-auto flex min-h-screen max-w-[1600px]"><AdminSidePanel :tenant="tenant" current="settings" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" /><section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10"><button type="button" class="mb-5 inline-flex rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button><div class="mx-auto max-w-3xl space-y-8"><header><p class="text-sm font-semibold text-red-700">Admin center</p><h1 class="mt-1 text-3xl font-bold text-neutral-900">Catalogue settings</h1></header><EnCard><form class="space-y-6" @submit.prevent="submit"><div><h2 class="text-lg font-semibold">Online catalogue mode</h2><p class="mt-1 text-sm text-neutral-500">Choose whether the Online Store uses the same catalogue as POS or a separate online-only catalogue.</p></div><label class="block rounded-lg border p-4" :class="form.catalogue_mode === 'shared' ? 'border-red-600 bg-red-50' : 'border-neutral-200'"><input v-model="form.catalogue_mode" type="radio" value="shared" class="mr-2" /> Shared products <span class="block pl-6 text-sm text-neutral-500">Products marked for Online Store can also be sold through POS.</span></label><label class="block rounded-lg border p-4" :class="form.catalogue_mode === 'separate_online' ? 'border-red-600 bg-red-50' : 'border-neutral-200'"><input v-model="form.catalogue_mode" type="radio" value="separate_online" class="mr-2" /> Separate Online Store products <span class="block pl-6 text-sm text-neutral-500">Only products not enabled for POS appear in the Online Store.</span></label><EnButton type="submit" :loading="form.processing">Save catalogue settings</EnButton></form></EnCard></div></section></div></main>
</template>