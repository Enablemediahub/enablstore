<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    metrics: {
        sales_count: number;
        revenue_minor: number;
        items_sold: number;
    };
    topProducts: Array<{
        quantity: number;
        product: { name: string };
    }>;
}>();

const formatGhs = (minor: number): string =>
    new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(minor / 100);

const mobilePanelOpen = ref(false);
</script>

<template>
    <Head title="Analytics" />
    <main class="min-h-screen bg-neutral-50">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel
                :tenant="String(route().params.tenant)"
                current="analytics"
                :mobile-open="mobilePanelOpen"
                @close="mobilePanelOpen = false"
            />
            <section class="min-w-0 flex-1 px-4 py-10 sm:px-8">
                <button
                    type="button"
                    class="mb-5 inline-flex items-center gap-2 rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm font-medium text-neutral-700 shadow-sm lg:hidden"
                    @click="mobilePanelOpen = true"
                >
                    Open admin navigation
                </button>
                <div class="mx-auto max-w-7xl space-y-8">
            <header>
                <p class="text-primary-700 text-sm font-semibold">
                    Performance
                </p>
                <h1 class="mt-1 text-3xl font-bold text-neutral-900">
                    Analytics
                </h1>
            </header>
            <div class="grid gap-5 md:grid-cols-3">
                <EnCard
                    ><p class="text-sm text-neutral-500">Completed sales</p>
                    <p class="mt-2 text-3xl font-bold text-neutral-900">
                        {{ metrics.sales_count }}
                    </p></EnCard
                >
                <EnCard
                    ><p class="text-sm text-neutral-500">Revenue</p>
                    <p
                        class="mt-2 font-mono text-3xl font-bold text-neutral-900"
                    >
                        {{ formatGhs(metrics.revenue_minor) }}
                    </p></EnCard
                >
                <EnCard
                    ><p class="text-sm text-neutral-500">Items sold</p>
                    <p class="mt-2 text-3xl font-bold text-neutral-900">
                        {{ metrics.items_sold }}
                    </p></EnCard
                >
            </div>
            <EnCard>
                <h2 class="text-lg font-semibold text-neutral-900">
                    Top products
                </h2>
                <div
                    v-if="topProducts.length"
                    class="mt-5 divide-y divide-neutral-100"
                >
                    <div
                        v-for="item in topProducts"
                        :key="item.product.name"
                        class="flex justify-between py-4 text-sm"
                    >
                        <span class="font-medium text-neutral-900">{{
                            item.product.name
                        }}</span>
                        <span class="font-mono text-neutral-500"
                            >{{ item.quantity }} sold</span
                        >
                    </div>
                </div>
                <p v-else class="mt-5 text-sm text-neutral-500">
                    Sales analytics will appear after the first completed sale.
                </p>
            </EnCard>
                </div>
            </section>
        </div>
    </main>
</template>
