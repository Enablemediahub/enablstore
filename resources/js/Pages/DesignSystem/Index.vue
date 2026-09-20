<script setup lang="ts">
import EnBadge from '@/Components/EnBadge.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import EnEmptyState from '@/Components/EnEmptyState.vue';
import EnInput from '@/Components/EnInput.vue';
import EnSelect from '@/Components/EnSelect.vue';
import EnSkeleton from '@/Components/EnSkeleton.vue';
import EnTextarea from '@/Components/EnTextarea.vue';
import { ref } from 'vue';

const name = ref('');
const category = ref('');
const notes = ref('');

const categories = [
    { label: 'Grocery', value: 'grocery' },
    { label: 'Household', value: 'household' },
    { label: 'Personal care', value: 'personal-care' },
];
</script>

<template>
    <main class="min-h-screen bg-neutral-50 px-4 py-10 sm:px-8 lg:px-12">
        <div class="mx-auto max-w-6xl space-y-10">
            <header class="max-w-2xl">
                <p
                    class="text-primary-700 text-sm font-semibold tracking-wide uppercase"
                >
                    Enablstore foundation
                </p>
                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-neutral-900"
                >
                    Design system
                </h1>
                <p class="mt-3 text-neutral-500">
                    Reusable interface primitives for retail operations,
                    billing, and storefront workflows.
                </p>
            </header>

            <section aria-labelledby="buttons-heading">
                <h2
                    id="buttons-heading"
                    class="mb-4 text-xl font-semibold text-neutral-900"
                >
                    Buttons and badges
                </h2>
                <EnCard>
                    <div class="flex flex-wrap items-center gap-3">
                        <EnButton>Save product</EnButton>
                        <EnButton variant="accent">Start trial</EnButton>
                        <EnButton variant="ghost">Cancel</EnButton>
                        <EnButton variant="danger">Delete</EnButton>
                        <EnButton :loading="true">Saving</EnButton>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <EnBadge>Draft</EnBadge>
                        <EnBadge tone="success">Active</EnBadge>
                        <EnBadge tone="warning">Pending</EnBadge>
                        <EnBadge tone="danger">Failed</EnBadge>
                        <EnBadge tone="info">Online</EnBadge>
                    </div>
                </EnCard>
            </section>

            <section aria-labelledby="forms-heading">
                <h2
                    id="forms-heading"
                    class="mb-4 text-xl font-semibold text-neutral-900"
                >
                    Form controls
                </h2>
                <EnCard>
                    <div class="grid gap-5 md:grid-cols-2">
                        <EnInput
                            id="product-name"
                            v-model="name"
                            label="Product name"
                            required
                        />
                        <EnSelect
                            id="category"
                            v-model="category"
                            label="Category"
                            :options="categories"
                        />
                        <div class="md:col-span-2">
                            <EnTextarea
                                id="notes"
                                v-model="notes"
                                label="Notes"
                            />
                        </div>
                    </div>
                </EnCard>
            </section>

            <section aria-labelledby="states-heading">
                <h2
                    id="states-heading"
                    class="mb-4 text-xl font-semibold text-neutral-900"
                >
                    Loading and empty states
                </h2>
                <div class="grid gap-6 lg:grid-cols-2">
                    <EnCard>
                        <h3 class="mb-4 font-semibold text-neutral-900">
                            Skeletons
                        </h3>
                        <div class="space-y-6">
                            <EnSkeleton variant="text" />
                            <EnSkeleton variant="table" />
                        </div>
                    </EnCard>
                    <EnCard>
                        <EnEmptyState
                            title="No products yet"
                            description="Add your first product to start building the catalogue."
                        >
                            <template #action>
                                <EnButton variant="accent"
                                    >Add product</EnButton
                                >
                            </template>
                        </EnEmptyState>
                    </EnCard>
                </div>
            </section>
        </div>
    </main>
</template>
