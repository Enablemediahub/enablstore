<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import SuperAdminSidePanel from '@/Components/SuperAdminSidePanel.vue';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays, CreditCard, Search, WalletCards, X } from '@lucide/vue';
import { onUnmounted, ref, watch } from 'vue';

type PaymentRow = {
    id: number;
    reference: string;
    subscriber_code: string | null;
    subscriber: string;
    email: string | null;
    phone: string | null;
    plan: string;
    expires_at: string | null;
    amount_minor: number;
    currency: string;
    provider: 'paystack' | 'manual' | string;
    status: string;
    paid_at: string | null;
    created_at: string | null;
};

const props = defineProps<{
    payments: {
        data: PaymentRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: { search: string; provider: string; status: string; date_from: string; date_to: string; renewal_window: string };
    metrics: { revenue_minor: number; paid_count: number; paystack_minor: number; manual_minor: number; pending_count: number; upcoming_renewals_count: number };
}>();

const search = ref(props.filters.search);
const provider = ref(props.filters.provider);
const status = ref(props.filters.status);
const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);
const renewalWindow = ref(props.filters.renewal_window);
let searchTimeout: ReturnType<typeof setTimeout> | undefined;

const visitFilteredResults = (): void => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = undefined;
    router.get(route('super-admin.financial.index'), {
        search: search.value.trim() || undefined,
        provider: provider.value !== 'all' ? provider.value : undefined,
        status: status.value !== 'all' ? status.value : undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        renewal_window: renewalWindow.value !== 'all' ? renewalWindow.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['payments', 'filters', 'metrics'],
    });
};

watch([search, provider, status, dateFrom, dateTo, renewalWindow], () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(visitFilteredResults, 300);
});

const clearFilters = (): void => {
    search.value = '';
    provider.value = 'all';
    status.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    renewalWindow.value = 'all';
};

const formatMoney = (minor: number, currency = 'GHS'): string => new Intl.NumberFormat('en-GH', {
    style: 'currency',
    currency,
    maximumFractionDigits: 2,
}).format(minor / 100);

const formatDate = (value: string | null): string => value
    ? new Intl.DateTimeFormat('en-GH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    : 'Pending';

const expiryDate = (value: string | null): string => value
    ? new Intl.DateTimeFormat('en-GH', { dateStyle: 'medium' }).format(new Date(value))
    : 'Not scheduled';

const expiryLabel = (value: string | null): string => {
    if (!value) return 'No expiry date';
    const expiry = new Date(value);
    const today = new Date();
    const expiryDay = new Date(expiry.getFullYear(), expiry.getMonth(), expiry.getDate()).getTime();
    const todayDay = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
    const daysRemaining = Math.round((expiryDay - todayDay) / 86400000);
    if (daysRemaining < 0) return `Expired ${Math.abs(daysRemaining)} days ago`;
    if (daysRemaining === 0) return 'Expires today';
    if (daysRemaining === 1) return 'Expires tomorrow';
    if (daysRemaining <= 7) return `Expires in ${daysRemaining} days`;
    return `Expires ${expiryDate(value)}`;
};

const expiryClass = (value: string | null): string => {
    if (!value) return 'text-neutral-500';
    const daysRemaining = Math.ceil((new Date(value).getTime() - Date.now()) / 86400000);
    if (daysRemaining < 0) return 'font-semibold text-red-700';
    if (daysRemaining <= 7) return 'font-semibold text-amber-700';
    return 'text-neutral-600';
};

const providerLabel = (value: string): string => value === 'paystack' ? 'Company Paystack' : value === 'manual' ? 'Offline / manual' : value;
const statusClass = (value: string): string => ({
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-amber-100 text-amber-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-neutral-200 text-neutral-700',
}[value] ?? 'bg-neutral-100 text-neutral-700');

onUnmounted(() => {
    if (searchTimeout) clearTimeout(searchTimeout);
});
</script>

<template>
    <Head title="Financial" />
    <main class="flex min-h-screen bg-neutral-50 text-neutral-900">
        <SuperAdminSidePanel current="financial" />
        <section class="min-w-0 flex-1 px-4 py-8 sm:px-8 xl:px-10">
            <div class="mx-auto max-w-7xl space-y-6">
                <header class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[#e21b23]">Subscriber billing</p>
                        <h1 class="mt-1 text-3xl font-black">Financial</h1>
                        <p class="mt-2 text-sm text-neutral-500">Subscription collections across all subscriber workspaces.</p>
                    </div>
                    <p class="text-xs font-medium text-neutral-500">{{ payments.total }} matching transactions</p>
                </header>

                <section class="grid grid-cols-2 gap-3 xl:grid-cols-6" aria-label="Subscription revenue summary">
                    <EnCard class="col-span-2 border-l-4 border-l-[#e21b23] xl:col-span-1">
                        <div class="flex items-center justify-between gap-2"><p class="text-xs font-semibold text-neutral-500">Revenue collected</p><WalletCards :size="18" class="text-[#e21b23]" aria-hidden="true" /></div>
                        <p class="mt-3 break-words text-2xl font-black text-neutral-900">{{ formatMoney(metrics.revenue_minor) }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Paid subscriptions</p>
                    </EnCard>
                    <EnCard>
                        <div class="flex items-center justify-between gap-2"><p class="text-xs font-semibold text-neutral-500">Paid transactions</p><CreditCard :size="18" class="text-green-700" aria-hidden="true" /></div>
                        <p class="mt-3 text-2xl font-black">{{ metrics.paid_count }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Verified or recorded</p>
                    </EnCard>
                    <EnCard>
                        <p class="text-xs font-semibold text-neutral-500">Company Paystack</p>
                        <p class="mt-3 break-words text-xl font-black">{{ formatMoney(metrics.paystack_minor) }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Online collections</p>
                    </EnCard>
                    <EnCard>
                        <p class="text-xs font-semibold text-neutral-500">Offline / manual</p>
                        <p class="mt-3 break-words text-xl font-black">{{ formatMoney(metrics.manual_minor) }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Cash and other methods</p>
                    </EnCard>
                    <EnCard>
                        <p class="text-xs font-semibold text-neutral-500">Pending payments</p>
                        <p class="mt-3 text-2xl font-black text-amber-700">{{ metrics.pending_count }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Awaiting confirmation</p>
                    </EnCard>
                    <EnCard class="col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold text-neutral-500">Renewals due in 7 days</p>
                        <p class="mt-3 text-2xl font-black text-[#e21b23]">{{ metrics.upcoming_renewals_count }}</p>
                        <button type="button" class="mt-1 text-left text-xs font-semibold text-[#b9151b] hover:underline" @click="renewalWindow = renewalWindow === '7_days' ? 'all' : '7_days'">{{ renewalWindow === '7_days' ? 'Show all payments' : 'View subscribers to call' }}</button>
                    </EnCard>
                </section>

                <EnCard>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_160px_150px_160px_160px_180px_auto] xl:items-end">
                        <label class="relative block">
                            <span class="mb-1.5 block text-xs font-semibold text-neutral-600">Search subscribers and references</span>
                            <Search :size="17" class="pointer-events-none absolute top-[2.55rem] left-3 -translate-y-1/2 text-neutral-400" aria-hidden="true" />
                            <input v-model="search" type="search" placeholder="Business, email, plan, reference" class="min-h-11 w-full rounded-md border border-neutral-300 bg-white py-2 pr-3 pl-10 text-sm focus:border-[#e21b23] focus:outline-none focus:ring-2 focus:ring-[#e21b23]/20" />
                        </label>
                        <label class="block text-xs font-semibold text-neutral-600">Payment method<select v-model="provider" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="all">All methods</option><option value="paystack">Company Paystack</option><option value="manual">Offline / manual</option></select></label>
                        <label class="block text-xs font-semibold text-neutral-600">Payment status<select v-model="status" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="all">All statuses</option><option value="paid">Paid</option><option value="pending">Pending</option><option value="failed">Failed</option><option value="refunded">Refunded</option></select></label>
                        <label class="block text-xs font-semibold text-neutral-600">Recorded from<span class="relative mt-1.5 block"><CalendarDays :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" aria-hidden="true" /><input v-model="dateFrom" type="date" class="min-h-11 w-full rounded-md border border-neutral-300 bg-white py-2 pr-2 pl-9 text-sm" /></span></label>
                        <label class="block text-xs font-semibold text-neutral-600">Recorded to<span class="relative mt-1.5 block"><CalendarDays :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" aria-hidden="true" /><input v-model="dateTo" type="date" :min="dateFrom || undefined" class="min-h-11 w-full rounded-md border border-neutral-300 bg-white py-2 pr-2 pl-9 text-sm" /></span></label>
                        <label class="block text-xs font-semibold text-neutral-600">Renewal timing<select v-model="renewalWindow" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="all">Any expiry date</option><option value="7_days">Due within 7 days</option></select></label>
                        <button type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-neutral-300 px-3 text-sm font-semibold text-neutral-700 hover:bg-neutral-100" @click="clearFilters"><X :size="16" aria-hidden="true" /> Clear</button>
                    </div>
                </EnCard>

                <EnCard class="overflow-hidden p-0">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-neutral-200 px-4 py-4 sm:px-5">
                        <div><h2 class="font-bold">Subscription transactions</h2><p class="mt-1 text-xs text-neutral-500">Totals above reflect the active filters.</p></div>
                        <span class="text-xs font-medium text-neutral-500">Page {{ payments.current_page }} of {{ payments.last_page }}</span>
                    </div>

                    <div v-if="payments.data.length" class="divide-y divide-neutral-100 md:hidden">
                        <article v-for="payment in payments.data" :key="payment.id" class="space-y-3 px-4 py-4">
                            <div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="truncate font-semibold">{{ payment.subscriber }}</p><p class="mt-1 text-xs text-neutral-500">{{ payment.subscriber_code || payment.email || payment.reference }}</p><a v-if="payment.phone" :href="`tel:${payment.phone}`" class="mt-1 inline-block font-semibold text-[#b9151b]">Call {{ payment.phone }}</a></div><strong class="shrink-0 text-right font-mono text-sm">{{ formatMoney(payment.amount_minor, payment.currency) }}</strong></div>
                            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2 text-xs"><div class="min-w-0"><p class="truncate font-medium text-neutral-700">{{ payment.plan }}</p><p class="mt-1 break-all font-mono text-[11px] text-neutral-500">{{ payment.reference }}</p></div><span class="shrink-0 rounded-full px-2.5 py-1 font-bold capitalize" :class="statusClass(payment.status)">{{ payment.status }}</span></div>
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs"><span :class="expiryClass(payment.expires_at)">{{ expiryLabel(payment.expires_at) }} <span v-if="payment.expires_at" class="font-normal text-neutral-500">· {{ expiryDate(payment.expires_at) }}</span></span><span class="text-neutral-500">{{ formatDate(payment.paid_at || payment.created_at) }}</span></div>
                            <div class="text-xs text-neutral-500">{{ providerLabel(payment.provider) }}</div>
                        </article>
                    </div>

                    <div v-if="payments.data.length" class="hidden overflow-x-auto md:block">
                        <table class="w-full min-w-[1080px] text-left text-sm">
                            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500"><tr><th class="px-4 py-3">Subscriber</th><th class="px-4 py-3">Plan</th><th class="px-4 py-3">Expires</th><th class="px-4 py-3">Reference</th><th class="px-4 py-3">Method</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Recorded</th></tr></thead>
                            <tbody><tr v-for="payment in payments.data" :key="payment.id" class="border-b border-neutral-100 last:border-0"><td class="px-4 py-3"><p class="font-semibold">{{ payment.subscriber }}</p><p class="mt-1 text-xs text-neutral-500">{{ payment.subscriber_code || payment.email || payment.reference }}</p><a v-if="payment.phone" :href="`tel:${payment.phone}`" class="mt-1 inline-block text-xs font-semibold text-[#b9151b]">{{ payment.phone }}</a></td><td class="px-4 py-3">{{ payment.plan }}</td><td class="px-4 py-3"><p :class="expiryClass(payment.expires_at)">{{ expiryDate(payment.expires_at) }}</p><p v-if="payment.expires_at" class="mt-1 text-xs" :class="expiryClass(payment.expires_at)">{{ expiryLabel(payment.expires_at) }}</p></td><td class="px-4 py-3 font-mono text-xs">{{ payment.reference }}</td><td class="px-4 py-3">{{ providerLabel(payment.provider) }}</td><td class="px-4 py-3 font-mono font-semibold">{{ formatMoney(payment.amount_minor, payment.currency) }}</td><td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize" :class="statusClass(payment.status)">{{ payment.status }}</span></td><td class="px-4 py-3 text-xs text-neutral-600">{{ formatDate(payment.paid_at || payment.created_at) }}</td></tr></tbody>
                        </table>
                    </div>

                    <div v-else class="px-5 py-14 text-center">
                        <WalletCards :size="28" class="mx-auto text-neutral-300" aria-hidden="true" />
                        <h3 class="mt-3 font-semibold">No subscription transactions found</h3>
                        <p class="mt-1 text-sm text-neutral-500">Try changing or clearing the active filters.</p>
                    </div>

                    <nav v-if="payments.last_page > 1" class="flex flex-wrap justify-center gap-2 border-t border-neutral-200 px-4 py-4" aria-label="Financial page navigation">
                        <button v-for="link in payments.links" :key="link.label" type="button" class="min-h-9 rounded-md border px-3 text-sm" :class="link.active ? 'border-[#e21b23] bg-[#e21b23] text-white' : 'border-neutral-200 bg-white text-neutral-700'" :disabled="!link.url" @click="link.url && router.visit(link.url, { preserveScroll: true })"><span v-html="link.label" /></button>
                    </nav>
                </EnCard>
            </div>
        </section>
    </main>
</template>