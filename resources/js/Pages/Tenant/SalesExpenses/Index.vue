<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarDays, CircleDollarSign, CreditCard, Plus, ReceiptText, TrendingDown, TrendingUp } from '@lucide/vue';
import { ref } from 'vue';

type SaleRow = {
    id: number;
    transaction_uuid: string;
    source: string;
    payment_method: string;
    total_minor: number;
    completed_at: string;
};

type ExpenseRow = {
    id: number;
    category: string;
    description: string | null;
    amount_minor: number;
    payment_method: string;
    spent_at: string;
};

const props = defineProps<{
    filters: { from: string; to: string };
    metrics: {
        sales_count: number;
        online_revenue_minor: number;
        pos_revenue_minor: number;
        revenue_minor: number;
        expenses_minor: number;
        net_minor: number;
    };
    recentSales: SaleRow[];
    recentExpenses: ExpenseRow[];
    status?: string | null;
}>();

const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
const from = ref(props.filters.from);
const to = ref(props.filters.to);
const expenseForm = useForm({
    category: '',
    description: '',
    amount_ghs: '',
    payment_method: 'cash',
    spent_at: new Date().toLocaleDateString('en-CA'),
});

const formatMoney = (minor: number): string => new Intl.NumberFormat('en-GH', {
    style: 'currency',
    currency: 'GHS',
    maximumFractionDigits: 2,
}).format(minor / 100);

const formatDate = (value: string): string => new Intl.DateTimeFormat('en-GH', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date(value));

const filter = (): void => router.get(route('tenant.sales-expenses.index', { tenant }), {
    from: from.value,
    to: to.value,
}, { preserveState: true, preserveScroll: true, replace: true });

const recordExpense = (): void => expenseForm.post(route('tenant.sales-expenses.store', { tenant }), {
    preserveScroll: true,
    onSuccess: () => expenseForm.reset('description', 'amount_ghs'),
});
</script>

<template>
    <Head title="Sales & expenses" />
    <main class="min-h-screen bg-neutral-50">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel :tenant="tenant" current="sales-expenses" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" />
            <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <div class="mx-auto max-w-7xl space-y-6">
                    <header class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-red-700">Business finances</p>
                            <h1 class="mt-1 text-3xl font-black text-neutral-900">Sales & expenses</h1>
                            <p class="mt-2 text-sm text-neutral-500">Online and in-person revenue alongside recorded business spending.</p>
                        </div>
                        <form class="grid grid-cols-2 gap-2 sm:flex sm:items-end" @submit.prevent="filter">
                            <label class="text-xs font-semibold text-neutral-600">From<span class="relative mt-1 block"><CalendarDays :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" aria-hidden="true" /><input v-model="from" type="date" class="min-h-11 w-full rounded-md border border-neutral-300 bg-white py-2 pr-2 pl-9 text-sm" /></span></label>
                            <label class="text-xs font-semibold text-neutral-600">To<span class="relative mt-1 block"><CalendarDays :size="16" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" aria-hidden="true" /><input v-model="to" type="date" :min="from" class="min-h-11 w-full rounded-md border border-neutral-300 bg-white py-2 pr-2 pl-9 text-sm" /></span></label>
                            <button type="submit" class="col-span-2 inline-flex min-h-11 items-center justify-center rounded-md bg-neutral-900 px-4 text-sm font-semibold text-white hover:bg-neutral-700 sm:col-span-1">Apply dates</button>
                        </form>
                    </header>

                    <p v-if="status" class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ status }}</p>

                    <section class="grid grid-cols-2 gap-3 lg:grid-cols-3 2xl:grid-cols-6" aria-label="Sales and expense summary">
                        <EnCard>
                            <div class="flex items-center justify-between gap-2"><p class="text-xs font-semibold text-neutral-500">Completed sales</p><ReceiptText :size="18" class="text-neutral-500" aria-hidden="true" /></div>
                            <p class="mt-3 text-2xl font-black">{{ metrics.sales_count }}</p>
                            <p class="mt-1 text-xs text-neutral-500">Across both channels</p>
                        </EnCard>
                        <EnCard>
                            <div class="flex items-center justify-between gap-2"><p class="text-xs font-semibold text-neutral-500">Online sales</p><TrendingUp :size="18" class="text-blue-700" aria-hidden="true" /></div>
                            <p class="mt-3 break-words text-xl font-black">{{ formatMoney(metrics.online_revenue_minor) }}</p>
                            <p class="mt-1 text-xs text-neutral-500">Storefront revenue</p>
                        </EnCard>
                        <EnCard>
                            <div class="flex items-center justify-between gap-2"><p class="text-xs font-semibold text-neutral-500">POS sales</p><CreditCard :size="18" class="text-emerald-700" aria-hidden="true" /></div>
                            <p class="mt-3 break-words text-xl font-black">{{ formatMoney(metrics.pos_revenue_minor) }}</p>
                            <p class="mt-1 text-xs text-neutral-500">In-person revenue</p>
                        </EnCard>
                        <EnCard>
                            <p class="text-xs font-semibold text-neutral-500">Total revenue</p>
                            <p class="mt-3 break-words text-xl font-black">{{ formatMoney(metrics.revenue_minor) }}</p>
                            <p class="mt-1 text-xs text-neutral-500">Completed orders</p>
                        </EnCard>
                        <EnCard>
                            <div class="flex items-center justify-between gap-2"><p class="text-xs font-semibold text-neutral-500">Expenses</p><TrendingDown :size="18" class="text-amber-700" aria-hidden="true" /></div>
                            <p class="mt-3 break-words text-xl font-black">{{ formatMoney(metrics.expenses_minor) }}</p>
                            <p class="mt-1 text-xs text-neutral-500">Manually recorded</p>
                        </EnCard>
                        <EnCard class="col-span-2 border-l-4 border-l-emerald-700 lg:col-span-1">
                            <div class="flex items-center justify-between gap-2"><p class="text-xs font-semibold text-neutral-500">Net after expenses</p><CircleDollarSign :size="18" class="text-emerald-700" aria-hidden="true" /></div>
                            <p class="mt-3 break-words text-xl font-black" :class="metrics.net_minor < 0 ? 'text-red-700' : 'text-emerald-800'">{{ formatMoney(metrics.net_minor) }}</p>
                            <p class="mt-1 text-xs text-neutral-500">Revenue less expenses</p>
                        </EnCard>
                    </section>

                    <EnCard>
                        <div class="flex items-start gap-3"><Plus :size="21" class="mt-0.5 text-red-700" aria-hidden="true" /><div><h2 class="text-lg font-bold">Record an expense</h2><p class="mt-1 text-sm text-neutral-500">Add spending to this workspace's financial summary.</p></div></div>
                        <form class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-6" @submit.prevent="recordExpense">
                            <label class="block text-sm font-medium text-neutral-700 xl:col-span-1">Category<input v-model="expenseForm.category" maxlength="80" placeholder="e.g. Transport" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm" /><span v-if="expenseForm.errors.category" class="mt-1 block text-xs text-red-700">{{ expenseForm.errors.category }}</span></label>
                            <label class="block text-sm font-medium text-neutral-700 xl:col-span-1">Amount (GHS)<input v-model="expenseForm.amount_ghs" type="number" min="0.01" max="1000000" step="0.01" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm" /><span v-if="expenseForm.errors.amount_ghs" class="mt-1 block text-xs text-red-700">{{ expenseForm.errors.amount_ghs }}</span></label>
                            <label class="block text-sm font-medium text-neutral-700 xl:col-span-1">Date<input v-model="expenseForm.spent_at" type="date" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm" /><span v-if="expenseForm.errors.spent_at" class="mt-1 block text-xs text-red-700">{{ expenseForm.errors.spent_at }}</span></label>
                            <label class="block text-sm font-medium text-neutral-700 xl:col-span-1">Paid with<select v-model="expenseForm.payment_method" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="cash">Cash</option><option value="bank_transfer">Bank transfer</option><option value="mobile_money">Mobile money</option><option value="card">Card</option><option value="other">Other</option></select></label>
                            <label class="block text-sm font-medium text-neutral-700 sm:col-span-2 xl:col-span-2">Description<input v-model="expenseForm.description" maxlength="500" placeholder="Optional details" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm" /><span v-if="expenseForm.errors.description" class="mt-1 block text-xs text-red-700">{{ expenseForm.errors.description }}</span></label>
                            <div class="flex justify-end sm:col-span-2 xl:col-span-6"><button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-red-700 px-5 text-sm font-bold text-white hover:bg-red-800 disabled:opacity-60" :disabled="expenseForm.processing"><Plus :size="17" aria-hidden="true" />{{ expenseForm.processing ? 'Recording...' : 'Record expense' }}</button></div>
                        </form>
                    </EnCard>

                    <div class="grid gap-6 xl:grid-cols-2">
                        <EnCard class="overflow-hidden p-0">
                            <div class="border-b border-neutral-200 px-4 py-4 sm:px-5"><h2 class="font-bold">Recent sales</h2><p class="mt-1 text-xs text-neutral-500">Completed online and POS transactions in this date range.</p></div>
                            <div v-if="recentSales.length" class="overflow-x-auto"><table class="w-full min-w-[540px] text-left text-sm"><thead class="border-b border-neutral-200 text-xs font-semibold uppercase text-neutral-500"><tr><th class="px-4 py-3">Channel</th><th class="px-4 py-3">Transaction</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Completed</th></tr></thead><tbody><tr v-for="sale in recentSales" :key="sale.id" class="border-b border-neutral-100 last:border-0"><td class="px-4 py-3 font-semibold">{{ sale.source === 'storefront' ? 'Online' : 'POS' }}</td><td class="px-4 py-3 font-mono text-xs">{{ sale.transaction_uuid.slice(0, 8) }}</td><td class="px-4 py-3 capitalize">{{ sale.payment_method.replace('_', ' ') }}</td><td class="px-4 py-3 text-right font-semibold">{{ formatMoney(sale.total_minor) }}</td><td class="px-4 py-3 text-xs text-neutral-500">{{ formatDate(sale.completed_at) }}</td></tr></tbody></table></div>
                            <p v-else class="px-4 py-8 text-center text-sm text-neutral-500">No completed sales in this date range.</p>
                        </EnCard>

                        <EnCard class="overflow-hidden p-0">
                            <div class="border-b border-neutral-200 px-4 py-4 sm:px-5"><h2 class="font-bold">Recent expenses</h2><p class="mt-1 text-xs text-neutral-500">Manually recorded spending in this date range.</p></div>
                            <div v-if="recentExpenses.length" class="overflow-x-auto"><table class="w-full min-w-[500px] text-left text-sm"><thead class="border-b border-neutral-200 text-xs font-semibold uppercase text-neutral-500"><tr><th class="px-4 py-3">Category</th><th class="px-4 py-3">Description</th><th class="px-4 py-3">Paid with</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Date</th></tr></thead><tbody><tr v-for="expense in recentExpenses" :key="expense.id" class="border-b border-neutral-100 last:border-0"><td class="px-4 py-3 font-semibold">{{ expense.category }}</td><td class="max-w-40 truncate px-4 py-3 text-neutral-600">{{ expense.description || '—' }}</td><td class="px-4 py-3 capitalize">{{ expense.payment_method.replace('_', ' ') }}</td><td class="px-4 py-3 text-right font-semibold">{{ formatMoney(expense.amount_minor) }}</td><td class="px-4 py-3 text-xs text-neutral-500">{{ formatDate(expense.spent_at) }}</td></tr></tbody></table></div>
                            <p v-else class="px-4 py-8 text-center text-sm text-neutral-500">No expenses recorded in this date range.</p>
                        </EnCard>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>