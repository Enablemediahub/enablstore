<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{ categories: Array<{ id: number; name: string; products_count: number }> }>();
const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
const form = useForm({ name: '' });

const submit = (): void => form.post(route('tenant.categories.store', { tenant }), { onSuccess: () => form.reset() });
</script>

<template>
    <Head title="Categories" />
    <main class="min-h-screen bg-neutral-50"><div class="mx-auto flex min-h-screen max-w-[1600px]"><AdminSidePanel :tenant="tenant" current="categories" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" /><section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10"><button type="button" class="mb-5 inline-flex rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button><div class="mx-auto max-w-4xl space-y-8"><header><p class="text-sm font-semibold text-red-700">Catalogue setup</p><h1 class="mt-1 text-3xl font-bold text-neutral-900">Categories</h1><p class="mt-2 text-sm text-neutral-500">Create categories for POS and Online Store products.</p></header><div class="grid gap-6 md:grid-cols-[1fr_1.5fr]"><EnCard><h2 class="text-lg font-semibold">Add category</h2><form class="mt-5 space-y-4" @submit.prevent="submit"><label class="block text-sm font-medium text-neutral-700">Category name<input v-model="form.name" required class="mt-1 min-h-11 w-full rounded-md border border-neutral-300 px-3" /></label><p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p><EnButton type="submit" :loading="form.processing">Add category</EnButton></form></EnCard><EnCard><h2 class="text-lg font-semibold">Existing categories</h2><div class="mt-4 divide-y divide-neutral-100"><div v-for="category in categories" :key="category.id" class="flex justify-between py-3 text-sm"><span class="font-medium">{{ category.name }}</span><span class="text-neutral-500">{{ category.products_count }} products</span></div><p v-if="!categories.length" class="py-3 text-sm text-neutral-500">No categories yet.</p></div></EnCard></div></div></section></div></main>
</template>