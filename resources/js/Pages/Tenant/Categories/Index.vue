<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Search, Trash2, X } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = defineProps<{ categories: Array<{ id: number; name: string; products_count: number }>; success?: string | null }>();
const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
const search = ref('');
const form = useForm({ name: '' });
const filteredCategories = computed(() => {
    const query = search.value.trim().toLowerCase();
    return props.categories.filter((category) => category.name.toLowerCase().includes(query));
});

const submit = (): void => form.post(route('tenant.categories.store', { tenant }), { onSuccess: () => form.reset() });
const deleteCategory = (category: { id: number; name: string; products_count: number }): void => {
    const linkedProducts = category.products_count > 0
        ? ' Linked products will remain in the catalogue as uncategorized.'
        : '';
    if (!window.confirm(`Delete "${category.name}"?${linkedProducts}`)) return;

    router.delete(route('tenant.categories.destroy', { tenant, category: category.id }), { preserveScroll: true });
};
</script>

<template>
    <Head title="Categories" />
    <main class="min-h-screen bg-neutral-50"><div class="mx-auto flex min-h-screen max-w-[1600px]"><AdminSidePanel :tenant="tenant" current="categories" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" /><section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10"><button type="button" class="mb-5 inline-flex rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button><div class="mx-auto max-w-4xl space-y-8"><header><p class="text-sm font-semibold text-red-700">Catalogue setup</p><h1 class="mt-1 text-3xl font-bold text-neutral-900">Categories</h1><p class="mt-2 text-sm text-neutral-500">Create categories for POS and Online Store products.</p></header><p v-if="success" role="status" class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ success }}</p><div class="grid gap-6 md:grid-cols-[1fr_1.5fr]"><EnCard><h2 class="text-lg font-semibold">Add category</h2><form class="mt-5 space-y-4" @submit.prevent="submit"><label class="block text-sm font-medium text-neutral-700">Category name<input v-model="form.name" required class="mt-1 min-h-11 w-full rounded-md border border-neutral-300 px-3" /></label><p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p><EnButton type="submit" :loading="form.processing">Add category</EnButton></form></EnCard><EnCard><div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-semibold">Existing categories</h2><span class="text-xs text-neutral-500">{{ filteredCategories.length }} of {{ categories.length }}</span></div><label class="relative mt-4 block"><span class="sr-only">Search categories</span><Search :size="17" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" aria-hidden="true" /><input v-model="search" type="search" placeholder="Search categories" class="min-h-11 w-full rounded-md border border-neutral-300 bg-white py-2 pr-10 pl-10 text-sm" /><button v-if="search" type="button" class="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-neutral-500 hover:bg-neutral-100" aria-label="Clear category search" @click="search = ''"><X :size="16" aria-hidden="true" /></button></label><div class="mt-3 divide-y divide-neutral-100"><div v-for="category in filteredCategories" :key="category.id" class="flex items-center justify-between gap-3 py-3 text-sm"><div class="min-w-0"><p class="truncate font-medium">{{ category.name }}</p><p class="mt-1 text-xs text-neutral-500">{{ category.products_count }} products</p></div><button type="button" class="inline-flex size-9 shrink-0 items-center justify-center rounded-md border border-red-200 text-red-700 hover:bg-red-50" :aria-label="`Delete ${category.name}`" :title="`Delete ${category.name}`" @click="deleteCategory(category)"><Trash2 :size="16" aria-hidden="true" /></button></div><p v-if="categories.length && !filteredCategories.length" class="py-3 text-sm text-neutral-500">No categories match your search.</p><p v-else-if="!categories.length" class="py-3 text-sm text-neutral-500">No categories yet.</p></div></EnCard></div></div></section></div></main>
</template>