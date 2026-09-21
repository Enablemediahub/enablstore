<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, CreditCard, Package, Search, ShoppingBag, TrendingUp, X } from '@lucide/vue';
import { computed, ref } from 'vue';

type DailySale = { date: string; sales_count: number; revenue_minor: number };
type PaymentMethod = { payment_method: string; sales_count: number; revenue_minor: number };

const props = defineProps<{
    filters: { from: string; to: string; search: string };
    metrics: { sales_count: number; revenue_minor: number; discounts_minor: number; average_sale_minor: number; items_sold: number };
    dailySales: DailySale[];
    paymentMethods: PaymentMethod[];
    topProducts: Array<{ quantity: number; revenue_minor: number; product: { name: string } | null }>;
    recentSales: Array<{ transaction_uuid: string; customer_name: string | null; payment_method: string; total_minor: number; completed_at: string }>;
}>();

const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
const from = ref(props.filters.from);
const to = ref(props.filters.to);
const search = ref(props.filters.search);
const maxRevenue = computed(() => Math.max(...props.dailySales.map((day) => day.revenue_minor), 1));
const maxProductRevenue = computed(() => Math.max(...props.topProducts.map((item) => item.revenue_minor), 1));

const formatGhs = (minor: number): string => new Intl.NumberFormat('en-GH', { style: 'currency', currency: 'GHS' }).format(minor / 100);
const paymentLabel = (method: string): string => method === 'mobile_money' ? 'Mobile Money' : method.charAt(0).toUpperCase() + method.slice(1);
const formatDate = (date: string): string => new Intl.DateTimeFormat('en-GH', { day: 'numeric', month: 'short' }).format(new Date(date));
const formatDateTime = (date: string): string => new Intl.DateTimeFormat('en-GH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date));

const applyFilters = (): void => router.get(route('tenant.analytics', { tenant }), { from: from.value, to: to.value, search: search.value.trim() || undefined }, { preserveState: true, replace: true });
const clearFilters = (): void => { search.value = ''; from.value = ''; to.value = ''; router.get(route('tenant.analytics', { tenant })); };
</script>

<template>
    <Head title="Sales & Analytics" />
    <main class="min-h-screen bg-neutral-50"><div class="mx-auto flex min-h-screen max-w-[1600px]">
        <AdminSidePanel :tenant="tenant" current="analytics" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" />
        <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10">
            <button type="button" class="mb-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-neutral-200 bg-white px-3 text-sm font-medium text-neutral-700 shadow-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button>
            <div class="mx-auto max-w-7xl space-y-6">
                <header class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold text-red-700">Business performance</p><h1 class="mt-1 text-3xl font-bold tracking-tight text-neutral-900">Sales &amp; Analytics</h1><p class="mt-2 text-sm text-neutral-500">Monitor revenue, payment mix, transaction volume, and product performance.</p></div><div class="rounded-lg bg-red-700 px-4 py-3 text-right text-white"><p class="text-xs font-semibold uppercase tracking-wide text-white/75">Selected revenue</p><p class="mt-1 font-mono text-xl font-bold">{{ formatGhs(metrics.revenue_minor) }}</p></div></header>

                <EnCard><form class="grid gap-3 lg:grid-cols-[1fr_170px_170px_auto_auto]" @submit.prevent="applyFilters"><label class="relative"><span class="sr-only">Search sales</span><Search :size="18" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" aria-hidden="true" /><input v-model="search" type="search" class="min-h-11 w-full rounded-md border border-neutral-300 py-2 pr-3 pl-10 text-sm" placeholder="Receipt, customer, or phone" /></label><input v-model="from" type="date" class="min-h-11 rounded-md border border-neutral-300 px-3 text-sm" aria-label="From date" /><input v-model="to" type="date" class="min-h-11 rounded-md border border-neutral-300 px-3 text-sm" aria-label="To date" /><button type="submit" class="min-h-11 rounded-md bg-red-700 px-5 text-sm font-semibold text-white hover:bg-red-800">Apply filters</button><button type="button" class="min-h-11 rounded-md border border-neutral-300 px-4 text-sm font-semibold text-neutral-700 hover:bg-neutral-50" @click="clearFilters"><X :size="16" class="inline" aria-hidden="true" /> Clear</button></form></EnCard>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"><EnCard><ShoppingBag :size="19" class="text-red-700" aria-hidden="true" /><p class="mt-4 text-sm text-neutral-500">Completed sales</p><p class="mt-1 text-3xl font-bold">{{ metrics.sales_count }}</p></EnCard><EnCard><TrendingUp :size="19" class="text-emerald-600" aria-hidden="true" /><p class="mt-4 text-sm text-neutral-500">Revenue</p><p class="mt-1 font-mono text-2xl font-bold">{{ formatGhs(metrics.revenue_minor) }}</p></EnCard><EnCard><BarChart3 :size="19" class="text-blue-600" aria-hidden="true" /><p class="mt-4 text-sm text-neutral-500">Average sale</p><p class="mt-1 font-mono text-2xl font-bold">{{ formatGhs(metrics.average_sale_minor) }}</p></EnCard><EnCard><Package :size="19" class="text-amber-600" aria-hidden="true" /><p class="mt-4 text-sm text-neutral-500">Items sold</p><p class="mt-1 text-3xl font-bold">{{ metrics.items_sold }}</p></EnCard><EnCard><CreditCard :size="19" class="text-violet-600" aria-hidden="true" /><p class="mt-4 text-sm text-neutral-500">Discounts given</p><p class="mt-1 font-mono text-2xl font-bold">{{ formatGhs(metrics.discounts_minor) }}</p></EnCard></div>

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(280px,0.8fr)]"><EnCard><div class="flex items-center justify-between"><div><h2 class="font-semibold text-neutral-900">Revenue trend</h2><p class="mt-1 text-sm text-neutral-500">Completed sales by day</p></div><span class="text-xs text-neutral-500">{{ filters.from }} – {{ filters.to }}</span></div><div v-if="dailySales.length" class="mt-6 flex h-56 items-end gap-2 border-b border-l border-neutral-200 px-3 pt-4"><div v-for="day in dailySales" :key="day.date" class="group flex h-full min-w-7 flex-1 flex-col justify-end"><div class="relative rounded-t bg-red-600 transition hover:bg-red-800" :style="{ height: `${Math.max((day.revenue_minor / maxRevenue) * 100, 3)}%` }"><span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden w-max -translate-x-1/2 rounded bg-neutral-900 px-2 py-1 text-[10px] text-white group-hover:block">{{ formatDate(day.date) }} · {{ formatGhs(day.revenue_minor) }}</span></div><span class="mt-2 text-center text-[10px] text-neutral-400">{{ formatDate(day.date) }}</span></div></div><p v-else class="mt-8 rounded-md bg-neutral-50 p-5 text-sm text-neutral-500">No completed sales match these filters.</p></EnCard>
                    <EnCard><h2 class="font-semibold text-neutral-900">Payment mix</h2><p class="mt-1 text-sm text-neutral-500">Revenue by payment method</p><div v-if="paymentMethods.length" class="mt-6 space-y-5"><div v-for="method in paymentMethods" :key="method.payment_method"><div class="flex justify-between gap-3 text-sm"><span class="font-medium">{{ paymentLabel(method.payment_method) }}</span><span class="font-mono text-neutral-600">{{ formatGhs(method.revenue_minor) }}</span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-neutral-100"><div class="h-full rounded-full bg-neutral-800" :style="{ width: `${(method.revenue_minor / Math.max(metrics.revenue_minor, 1)) * 100}%` }" /></div><p class="mt-1 text-xs text-neutral-500">{{ method.sales_count }} sales</p></div></div><p v-else class="mt-6 text-sm text-neutral-500">Payment analysis will appear when sales are recorded.</p></EnCard></div>

                <div class="grid gap-6 xl:grid-cols-2"><EnCard><h2 class="font-semibold text-neutral-900">Top products</h2><p class="mt-1 text-sm text-neutral-500">Ranked by revenue</p><div v-if="topProducts.length" class="mt-5 space-y-4"><div v-for="(item, index) in topProducts" :key="`${item.product?.name}-${index}`"><div class="flex items-center justify-between gap-4 text-sm"><p class="min-w-0 truncate font-medium text-neutral-900"><span class="mr-2 text-neutral-400">{{ index + 1 }}.</span>{{ item.product?.name ?? 'Archived product' }}</p><span class="font-mono text-neutral-600">{{ formatGhs(item.revenue_minor) }}</span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-neutral-100"><div class="h-full rounded-full bg-red-600" :style="{ width: `${(item.revenue_minor / maxProductRevenue) * 100}%` }" /></div><p class="mt-1 text-xs text-neutral-500">{{ item.quantity }} units sold</p></div></div><p v-else class="mt-5 text-sm text-neutral-500">Product performance will appear after completed sales.</p></EnCard>
                    <EnCard><h2 class="font-semibold text-neutral-900">Recent transactions</h2><p class="mt-1 text-sm text-neutral-500">Latest completed sales in the selected period</p><div v-if="recentSales.length" class="mt-5 divide-y divide-neutral-100"><div v-for="sale in recentSales" :key="sale.transaction_uuid" class="flex items-center justify-between gap-4 py-3"><div class="min-w-0"><p class="truncate font-mono text-xs font-semibold text-neutral-800">#{{ sale.transaction_uuid.slice(0, 8).toUpperCase() }}</p><p class="mt-1 truncate text-xs text-neutral-500">{{ sale.customer_name || 'Walk-in customer' }} · {{ paymentLabel(sale.payment_method) }}</p></div><div class="shrink-0 text-right"><p class="font-mono text-sm font-semibold">{{ formatGhs(sale.total_minor) }}</p><p class="mt-1 text-[11px] text-neutral-500">{{ formatDateTime(sale.completed_at) }}</p></div></div></div><p v-else class="mt-5 text-sm text-neutral-500">No transactions match these filters.</p></EnCard></div>
            </div>
        </section>
    </div></main>
</template>
