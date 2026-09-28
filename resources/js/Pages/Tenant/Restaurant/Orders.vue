<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import { Head, router } from '@inertiajs/vue3';
import { Check, ClipboardList, RefreshCw } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

type OrderStatus = 'queued' | 'preparing' | 'ready' | 'served' | 'cancelled';
type Order = {
    id: number;
    customerName: string | null;
    customerPhone: string | null;
    notes: string | null;
    status: OrderStatus;
    totalMinor: number;
    createdByName: string | null;
    createdAt: string | null;
    items: Array<{
        name: string;
        quantity: number;
        unitPriceMinor: number;
        lineTotalMinor: number;
        selectedOptions: Array<{ group: string; name: string; price_minor: number; quantity?: number }>;
    }>;
};

const props = defineProps<{
    tenant: string;
    restaurantName: string;
    orders: Order[];
}>();

const mobilePanelOpen = ref(false);
const showAll = ref(false);
let refreshTimer: ReturnType<typeof setInterval> | null = null;

const visibleOrders = computed(() => showAll.value
    ? props.orders
    : props.orders.filter((order) => !['served', 'cancelled'].includes(order.status)));
const activeCount = computed(() => props.orders.filter((order) => ['queued', 'preparing', 'ready'].includes(order.status)).length);

const formatPrice = (minor: number): string => new Intl.NumberFormat('en-GH', {
    style: 'currency',
    currency: 'GHS',
}).format(minor / 100);

const formatTime = (value: string | null): string => value
    ? new Intl.DateTimeFormat('en-GH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    : 'Time unavailable';

const refreshOrders = (): void => {
    router.reload({ only: ['orders'], preserveUrl: true });
};

const updateStatus = (order: Order, status: OrderStatus): void => {
    router.patch(route('tenant.foodstore.orders.status', { tenant: props.tenant, order: order.id }), { status }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const nextAction = (status: OrderStatus): { label: string; next: OrderStatus } | null => {
    if (status === 'queued') return { label: 'Start preparing', next: 'preparing' };
    if (status === 'preparing') return { label: 'Mark ready', next: 'ready' };
    if (status === 'ready') return { label: 'Mark served', next: 'served' };
    return null;
};

onMounted(() => {
    refreshTimer = setInterval(refreshOrders, 10_000);
});

onUnmounted(() => {
    if (refreshTimer !== null) clearInterval(refreshTimer);
});
</script>

<template>
    <Head :title="`Kitchen orders - ${restaurantName}`" />
    <main class="min-h-screen bg-neutral-50 text-neutral-900">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel :tenant="tenant" current="kitchen-orders" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" />
            <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <button type="button" class="mb-5 inline-flex min-h-10 items-center rounded-md border border-neutral-200 bg-white px-3 text-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button>
                <div class="mx-auto max-w-6xl">
                    <header class="flex flex-wrap items-end justify-between gap-4 border-b border-neutral-200 pb-5">
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">{{ restaurantName }}</p>
                            <h1 class="mt-1 flex items-center gap-3 text-3xl font-black"><ClipboardList :size="29" aria-hidden="true" /> Kitchen orders</h1>
                            <p class="mt-2 text-sm text-neutral-600">{{ activeCount }} active {{ activeCount === 1 ? 'order' : 'orders' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-neutral-300 bg-white px-3 text-sm font-semibold text-neutral-700 hover:bg-neutral-100" :aria-pressed="showAll" @click="showAll = !showAll">{{ showAll ? 'Active orders' : 'All recent' }}</button>
                            <button type="button" class="grid size-10 place-items-center rounded-md border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-100" aria-label="Refresh orders" title="Refresh orders" @click="refreshOrders"><RefreshCw :size="17" aria-hidden="true" /></button>
                        </div>
                    </header>

                    <div v-if="visibleOrders.length" class="mt-6 space-y-4">
                        <article v-for="order in visibleOrders" :key="order.id" class="rounded-md border border-neutral-200 bg-white shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-neutral-100 px-4 py-4 sm:px-5">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-lg font-black">Order #{{ order.id }}</h2>
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize" :class="{
                                            'bg-amber-100 text-amber-900': order.status === 'queued',
                                            'bg-blue-100 text-blue-900': order.status === 'preparing',
                                            'bg-emerald-100 text-emerald-900': order.status === 'ready' || order.status === 'served',
                                            'bg-red-100 text-red-900': order.status === 'cancelled',
                                        }">{{ order.status }}</span>
                                        <span v-if="order.createdByName?.includes('WhatsApp')" class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-900">WhatsApp</span>
                                    </div>
                                    <p class="mt-1 text-xs text-neutral-500">{{ formatTime(order.createdAt) }}</p>
                                </div>
                                <div class="text-right"><p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Order total</p><p class="mt-1 font-mono text-lg font-black">{{ formatPrice(order.totalMinor) }}</p></div>
                            </div>
                            <div class="grid gap-5 px-4 py-4 sm:grid-cols-[minmax(0,1fr)_240px] sm:px-5">
                                <div>
                                    <p class="text-sm font-bold">{{ order.customerName || 'Customer' }}</p>
                                    <a v-if="order.customerPhone" :href="`tel:${order.customerPhone}`" class="mt-0.5 inline-block text-sm text-emerald-800 hover:underline">{{ order.customerPhone }}</a>
                                    <p v-if="order.createdByName" class="mt-1 text-xs text-neutral-500">{{ order.createdByName }}</p>
                                    <ul class="mt-4 divide-y divide-neutral-100">
                                        <li v-for="(item, index) in order.items" :key="`${item.name}-${index}`" class="py-3 first:pt-0 last:pb-0">
                                            <div class="flex items-start justify-between gap-3"><p class="font-semibold">{{ item.quantity }} × {{ item.name }}</p><span class="shrink-0 font-mono text-sm">{{ formatPrice(item.lineTotalMinor) }}</span></div>
                                            <p v-for="(option, optionIndex) in item.selectedOptions" :key="`${option.group}-${option.name}-${optionIndex}`" class="mt-1 text-xs text-neutral-600">{{ option.group }}: {{ option.quantity ?? 1 }} × {{ option.name }}<span v-if="option.price_minor"> (+{{ formatPrice(option.price_minor) }} each)</span></p>
                                        </li>
                                    </ul>
                                    <p v-if="order.notes" class="mt-4 whitespace-pre-line rounded-md px-3 py-2 text-sm" :class="order.notes.startsWith('ALLERGY ALERT:') ? 'border border-red-300 bg-red-50 font-semibold text-red-900' : 'bg-amber-50 text-amber-950'"><strong>{{ order.notes.startsWith('ALLERGY ALERT:') ? 'Food allergy alert:' : 'Customer notes:' }}</strong> {{ order.notes.replace(/^ALLERGY ALERT:\s*/, '') }}</p>
                                </div>
                                <div class="flex flex-col justify-end gap-2 sm:border-l sm:border-neutral-100 sm:pl-5">
                                    <button v-if="nextAction(order.status)" type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-emerald-700 px-4 text-sm font-bold text-white hover:bg-emerald-800" @click="updateStatus(order, nextAction(order.status)!.next)"><Check :size="17" aria-hidden="true" />{{ nextAction(order.status)!.label }}</button>
                                    <button v-if="['queued', 'preparing', 'ready'].includes(order.status)" type="button" class="min-h-10 rounded-md border border-red-200 px-4 text-sm font-semibold text-red-700 hover:bg-red-50" @click="updateStatus(order, 'cancelled')">Cancel order</button>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div v-else class="mt-8 flex min-h-64 flex-col items-center justify-center border border-dashed border-neutral-300 bg-white px-6 text-center">
                        <ClipboardList :size="32" class="text-neutral-400" aria-hidden="true" />
                        <h2 class="mt-3 text-lg font-bold">{{ showAll ? 'No recent orders' : 'No active orders' }}</h2>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>