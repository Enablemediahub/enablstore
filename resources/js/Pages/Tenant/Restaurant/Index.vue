<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnEmptyState from '@/Components/EnEmptyState.vue';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LayoutDashboard, LayoutGrid, List, LogOut, Minus, Pause, Play, Plus, Rows3, ShoppingCart, Trash2, Utensils, UserRound } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

type MenuItem = {
    id: number;
    name: string;
    category: string;
    description: string | null;
    price_minor: number;
    unit_label: string;
    image_url: string | null;
    is_available: boolean;
};

type TenderMethod = 'cash' | 'mobile_money' | 'card';
type CartItem = MenuItem & { quantity: number };
type Tender = { id: string; method: TenderMethod; amountGhs: number | null; cashReceivedGhs: number | null; externallyConfirmed: boolean };
type HeldSale = { id: string; items: CartItem[]; tenders: Tender[]; discountType: 'fixed' | 'percentage' | ''; discountValue: number; discountReason: string; customerName: string; customerPhone: string };
type Receipt = {
    items: Array<{ name: string; quantity: number; priceMinor: number }>;
    totalMinor: number;
    paymentMethod: string;
    tenders: Array<{ method: string; amountMinor: number }>;
    transactionUuid: string;
    cashierName: string;
    customerName: string;
    customerPhone: string;
    cashReceivedMinor: number | null;
    changeMinor: number | null;
};

const props = defineProps<{
    tenant: string;
    restaurantName: string;
    heroImageUrl: string | null;
    todaySalesMinor: number;
    todaySalesCount: number;
    operatorName: string;
    cashierName: string | null;
    menuItems: MenuItem[];
    completedReceipt?: Receipt | null;
    status?: string | null;
}>();

const adminPortalOpen = ref(false);
const search = ref('');
const displayMode = ref<'grid' | 'thumbnail' | 'list'>('thumbnail');
const cart = ref<CartItem[]>([]);
const tenders = ref<Tender[]>([{ id: crypto.randomUUID(), method: 'cash', amountGhs: null, cashReceivedGhs: null, externallyConfirmed: false }]);
const discountType = ref<'fixed' | 'percentage' | ''>('');
const discountValue = ref(0);
const discountReason = ref('');
const customerName = ref('');
const customerPhone = ref('');
const heldSales = ref<HeldSale[]>([]);
const receipt = ref<Receipt | null>(props.completedReceipt ?? null);
const checkoutForm = useForm({
    transaction_uuid: crypto.randomUUID(),
    payment_method: 'cash' as TenderMethod | 'split',
    customer_name: '',
    customer_phone: '',
    discount_type: null as 'fixed' | 'percentage' | null,
    discount_value: 0,
    discount_reason: '',
    items: [] as Array<{ menu_item_id: number; quantity: number }>,
    tenders: [] as Array<{ method: TenderMethod; amount_minor: number; cash_received_minor: number | null; externally_confirmed: boolean }>,
});
const logoutForm = useForm({});
const checkoutError = computed(() => (checkoutForm.errors as Record<string, string>).checkout);

const filteredMenu = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.menuItems.filter((item) => `${item.name} ${item.category}`.toLowerCase().includes(term));
});
const heroImage = computed(() => props.heroImageUrl ?? props.menuItems.find((item) => item.image_url)?.image_url ?? '/images/storefront/hero-default.svg');
const subtotalMinor = computed(() => cart.value.reduce((total, item) => total + item.price_minor * item.quantity, 0));
const discountMinor = computed(() => discountType.value === 'percentage'
    ? Math.min(subtotalMinor.value, Math.round(subtotalMinor.value * Math.min(Math.max(discountValue.value, 0), 100) / 100))
    : Math.min(subtotalMinor.value, Math.round(Math.max(discountValue.value, 0) * 100)));
const totalMinor = computed(() => Math.max(0, subtotalMinor.value - discountMinor.value));
const tenderAmountMinor = (amount: number | null): number => Number.isFinite(Number(amount)) && Number(amount) > 0 ? Math.round(Number(amount) * 100) : 0;
const appliedTenderTotalMinor = computed(() => tenders.value.reduce((total, tender) => total + tenderAmountMinor(tender.amountGhs), 0));
const balanceDueMinor = computed(() => Math.max(0, totalMinor.value - appliedTenderTotalMinor.value));
const cashReceivedMinor = computed(() => tenders.value.filter((tender) => tender.method === 'cash').reduce((total, tender) => total + tenderAmountMinor(tender.cashReceivedGhs), 0));
const cashAllocationMinor = computed(() => tenders.value.filter((tender) => tender.method === 'cash').reduce((total, tender) => total + tenderAmountMinor(tender.amountGhs), 0));
const changeMinor = computed(() => Math.max(0, cashReceivedMinor.value - cashAllocationMinor.value));
const tenderTotalMatches = computed(() => appliedTenderTotalMinor.value === totalMinor.value);
const cashIsCovered = computed(() => tenders.value.filter((tender) => tender.method === 'cash').every((tender) => tender.cashReceivedGhs !== null && tenderAmountMinor(tender.cashReceivedGhs) >= tenderAmountMinor(tender.amountGhs)));
const externalPaymentsConfirmed = computed(() => tenders.value.every((tender) => tender.method === 'cash' || tender.externallyConfirmed));
const canCompleteSale = computed(() => cart.value.length > 0 && totalMinor.value > 0 && tenderTotalMatches.value && cashIsCovered.value && externalPaymentsConfirmed.value && tenders.value.every((tender) => tenderAmountMinor(tender.amountGhs) > 0));

watch(totalMinor, (total) => {
    if (tenders.value.length === 1 && tenders.value[0].method === 'cash') tenders.value[0].amountGhs = total > 0 ? total / 100 : null;
});
watch(() => props.completedReceipt, (value) => { if (value) receipt.value = value; });

const formatPrice = (minor: number): string => new Intl.NumberFormat('en-GH', {
    style: 'currency',
    currency: 'GHS',
}).format(minor / 100);

const addToCart = (item: MenuItem): void => {
    if (!item.is_available) return;

    const line = cart.value.find((entry) => entry.id === item.id);
    if (line) line.quantity += 1;
    else cart.value.push({ ...item, quantity: 1 });
};

const adjustQuantity = (itemId: number, amount: number): void => {
    const line = cart.value.find((item) => item.id === itemId);
    if (!line) return;

    line.quantity += amount;
    if (line.quantity <= 0) cart.value = cart.value.filter((item) => item.id !== itemId);
};

const addTender = (): void => {
    if (tenders.value.length < 3) tenders.value.push({ id: crypto.randomUUID(), method: 'cash', amountGhs: null, cashReceivedGhs: null, externallyConfirmed: false });
};

const removeTender = (id: string): void => {
    if (tenders.value.length > 1) tenders.value = tenders.value.filter((tender) => tender.id !== id);
};

const clearSale = (): void => {
    cart.value = [];
    tenders.value = [{ id: crypto.randomUUID(), method: 'cash', amountGhs: null, cashReceivedGhs: null, externallyConfirmed: false }];
    discountType.value = '';
    discountValue.value = 0;
    discountReason.value = '';
    customerName.value = '';
    customerPhone.value = '';
};

const holdSale = (): void => {
    if (cart.value.length === 0) return;
    heldSales.value.push({
        id: crypto.randomUUID(),
        items: cart.value.map((item) => ({ ...item })),
        tenders: tenders.value.map((tender) => ({ ...tender })),
        discountType: discountType.value,
        discountValue: discountValue.value,
        discountReason: discountReason.value,
        customerName: customerName.value,
        customerPhone: customerPhone.value,
    });
    clearSale();
};

const resumeSale = (heldSale: HeldSale): void => {
    if (cart.value.length > 0) holdSale();
    cart.value = heldSale.items.map((item) => ({ ...item }));
    tenders.value = heldSale.tenders.map((tender) => ({ ...tender }));
    discountType.value = heldSale.discountType;
    discountValue.value = heldSale.discountValue;
    discountReason.value = heldSale.discountReason;
    customerName.value = heldSale.customerName;
    customerPhone.value = heldSale.customerPhone;
    heldSales.value = heldSales.value.filter((sale) => sale.id !== heldSale.id);
};

const placeSale = (): void => {
    if (!canCompleteSale.value) return;

    const paymentTenders = tenders.value.map((tender) => ({
        method: tender.method,
        amount_minor: tenderAmountMinor(tender.amountGhs),
        cash_received_minor: tender.method === 'cash' ? tenderAmountMinor(tender.cashReceivedGhs) : null,
        externally_confirmed: tender.method !== 'cash' && tender.externallyConfirmed,
    }));
    checkoutForm.payment_method = paymentTenders.length > 1 ? 'split' : paymentTenders[0].method;
    checkoutForm.customer_name = customerName.value;
    checkoutForm.customer_phone = customerPhone.value;
    checkoutForm.discount_type = discountType.value || null;
    checkoutForm.discount_value = discountValue.value;
    checkoutForm.discount_reason = discountReason.value;
    checkoutForm.items = cart.value.map((item) => ({ menu_item_id: item.id, quantity: item.quantity }));
    checkoutForm.tenders = paymentTenders;
    checkoutForm.post(route('tenant.foodstore.sales.store', { tenant: props.tenant }), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = [];
            clearSale();
            checkoutForm.transaction_uuid = crypto.randomUUID();
        },
    });
};

const logout = (): void => logoutForm.post(route('tenant.pos.logout', { tenant: props.tenant }));
</script>

<template>
    <Head :title="`FoodStore - ${restaurantName}`" />
    <main class="min-h-screen bg-neutral-50 text-neutral-900">
        <div class="mx-auto flex min-h-screen max-w-[1800px]">
            <AdminSidePanel
                v-if="$page.props.auth.user?.role === 'admin' && adminPortalOpen"
                :tenant="tenant"
                current="foodstore"
                :mobile-open="adminPortalOpen"
                @close="adminPortalOpen = false"
            />
            <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-9">
                <section class="relative isolate mb-7 flex min-h-[400px] items-end overflow-hidden rounded-lg bg-emerald-950 bg-cover bg-center px-5 py-5 text-white sm:min-h-[290px] sm:px-8 sm:py-7" :style="{ backgroundImage: `url('${heroImage}')` }" :aria-label="`${restaurantName} FoodStore`">
                    <div class="absolute inset-0 -z-10 bg-emerald-950/70" aria-hidden="true" />
                    <div class="relative flex w-full flex-col items-start justify-between gap-5 sm:flex-row sm:items-end sm:gap-4">
                        <div class="flex min-w-0 max-w-full flex-col items-start gap-3">
                            <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/80 bg-white p-2 shadow-lg ring-4 ring-emerald-300/30 sm:size-24">
                                <img src="/images/Enablstore.png" alt="Enablstore" class="h-full w-full object-contain" />
                            </div>
                            <div class="min-w-0 max-w-full">
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-100">Enablstore FoodStore</p>
                                <h1 class="mt-1 truncate text-2xl font-black text-white sm:text-4xl">{{ restaurantName }}</h1>
                                <p class="mt-1 text-sm font-medium text-white/85">Food menu and sales</p>
                            </div>
                        </div>
                        <div class="flex w-full shrink-0 flex-col items-start gap-3 sm:w-auto sm:items-end sm:gap-2">
                            <div class="text-left sm:text-right">
                                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-100">Today's sales</p>
                                <p class="mt-0.5 whitespace-nowrap font-sales text-[42pt] font-light leading-[0.88] text-white sm:text-[65pt]">{{ formatPrice(todaySalesMinor) }}</p>
                                <p class="mt-1 text-xs text-white/75">{{ todaySalesCount }} transactions</p>
                            </div>
                            <div class="flex w-full flex-wrap items-center justify-between gap-2 sm:w-auto sm:justify-end">
                                <div class="inline-flex max-w-40 items-center gap-1.5 rounded-md border border-white/25 bg-emerald-950/65 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm sm:max-w-56 sm:px-3">
                                    <UserRound :size="15" class="shrink-0" aria-hidden="true" />
                                    <span class="truncate">{{ operatorName }}</span>
                                </div>
                                <button v-if="cashierName" type="button" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-white/70 bg-emerald-950/70 px-3 text-xs font-bold text-white hover:bg-emerald-900 disabled:opacity-60" :disabled="logoutForm.processing" @click="logout"><LogOut :size="15" aria-hidden="true" /> Sign out</button>
                            </div>
                        </div>
                    </div>
                </section>
                <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold tracking-[0.2em] text-emerald-800 uppercase">{{ restaurantName }}</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight">FoodStore</h1>
                        <p class="mt-2 text-sm text-neutral-600">Select food, build the cart, and complete the sale.</p>
                    </div>
                    <div class="flex items-center gap-3"><div class="flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-900"><Utensils :size="17" aria-hidden="true" /> Food sales</div><button v-if="$page.props.auth.user?.role === 'admin'" type="button" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-emerald-200 bg-white px-3 text-sm font-semibold text-emerald-900 hover:bg-emerald-50" @click="adminPortalOpen = !adminPortalOpen"><LayoutDashboard :size="16" aria-hidden="true" />{{ adminPortalOpen ? 'Close Admin Portal' : 'Admin Portal' }}</button></div>
                </header>
                <p v-if="status" class="mb-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ status }}</p>

                <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_390px]">
                    <section class="min-w-0 space-y-5">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div><p class="text-xs font-bold tracking-[0.18em] text-emerald-800 uppercase">Menu</p><h2 class="mt-1 text-xl font-black">Available food</h2></div>
                            <div class="flex w-full flex-wrap items-center justify-between gap-2 sm:w-auto sm:justify-end">
                                <input v-model="search" type="search" class="min-h-10 w-full max-w-xs flex-1 rounded-md border border-neutral-300 bg-white px-3 text-sm sm:w-56" placeholder="Find food" aria-label="Find food" />
                                <div class="flex items-center gap-1 rounded-md border border-neutral-200 bg-white p-1" aria-label="Food view">
                                    <button v-for="mode in [{ value: 'grid', label: 'Grid', icon: LayoutGrid }, { value: 'thumbnail', label: 'Thumbnail', icon: Rows3 }, { value: 'list', label: 'List', icon: List }]" :key="mode.value" type="button" class="rounded p-2" :class="displayMode === mode.value ? 'bg-emerald-50 text-emerald-800' : 'text-neutral-500 hover:bg-neutral-50'" :aria-label="`${mode.label} view`" :aria-pressed="displayMode === mode.value" @click="displayMode = mode.value as 'grid' | 'thumbnail' | 'list'"><component :is="mode.icon" :size="16" aria-hidden="true" /></button>
                                </div>
                            </div>
                        </div>
                        <div v-if="filteredMenu.length" :class="displayMode === 'grid' ? 'grid gap-4 sm:grid-cols-2 2xl:grid-cols-3' : displayMode === 'thumbnail' ? 'grid grid-cols-2 gap-3 sm:grid-cols-4 2xl:grid-cols-5' : 'space-y-2'">
                            <article v-for="item in filteredMenu" :key="item.id" class="overflow-hidden rounded-md border border-neutral-200 bg-white" :class="displayMode === 'list' ? 'p-1' : ''">
                                <button type="button" class="w-full text-left transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50" :class="displayMode === 'list' ? 'flex flex-row items-center gap-3 p-2' : 'flex flex-col items-start p-3 sm:p-4'" :disabled="!item.is_available" @click="addToCart(item)">
                                    <div class="overflow-hidden rounded-md bg-emerald-50" :class="displayMode === 'list' ? 'size-16 shrink-0' : displayMode === 'thumbnail' ? 'mb-2 aspect-square w-full' : 'mb-3 aspect-[4/3] w-full'">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                                        <div v-else class="grid h-full place-items-center text-emerald-700"><Utensils :size="32" aria-hidden="true" /></div>
                                    </div>
                                    <span class="min-w-0" :class="displayMode === 'list' ? 'flex flex-1 flex-wrap items-center justify-between gap-x-3 gap-y-1' : 'w-full'"><span class="block w-full text-xs font-bold tracking-wide text-emerald-800 uppercase">{{ item.category }}</span><span class="mt-1 block truncate text-base font-bold text-neutral-900">{{ item.name }}</span><span v-if="item.description && displayMode !== 'thumbnail'" class="mt-1 block line-clamp-2 text-xs text-neutral-500">{{ item.description }}</span><span class="mt-2 flex w-full items-center justify-between gap-2"><strong class="font-mono text-sm">{{ formatPrice(item.price_minor) }} <span class="font-sans font-normal text-neutral-500">/ {{ item.unit_label }}</span></strong><span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800"><Plus :size="15" aria-hidden="true" /> Add</span></span></span>
                                </button>
                            </article>
                        </div>
                        <EnEmptyState v-else title="No food items found" description="Add menu items or change the search." />
                    </section>

                    <aside class="xl:sticky xl:top-5">
                        <section class="overflow-hidden rounded-lg border border-emerald-800 bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-800 text-white shadow-lg">
                            <div class="p-4 sm:p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2"><ShoppingCart :size="19" class="text-emerald-200" aria-hidden="true" /><h2 class="font-bold">Current sale</h2></div>
                                    <div class="flex items-center gap-2"><span class="text-xs text-white/70">{{ cart.length }} lines</span><button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-white/35 bg-white/10 px-2.5 text-xs font-bold hover:bg-white/20 disabled:opacity-40" title="Hold current sale and start another" :disabled="cart.length === 0" @click="holdSale"><Pause :size="14" aria-hidden="true" /> Hold</button></div>
                                </div>
                                <div v-if="heldSales.length" class="mt-4 rounded-md border border-white/15 bg-black/15 p-3">
                                    <p class="text-xs font-bold tracking-wide text-white/70 uppercase">Held sales</p>
                                    <div class="mt-2 space-y-2"><button v-for="(heldSale, index) in heldSales" :key="heldSale.id" type="button" class="flex w-full items-center justify-between rounded-md bg-white/10 px-3 py-2 text-left text-sm transition hover:bg-white/20" @click="resumeSale(heldSale)"><span>Sale {{ index + 1 }} · {{ heldSale.items.length }} lines</span><Play :size="15" aria-hidden="true" /></button></div>
                                </div>
                                <div v-if="cart.length" class="mt-4 divide-y divide-white/15">
                                    <div v-for="item in cart" :key="item.id" class="flex items-center gap-2 py-3">
                                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ item.name }}</p><p class="mt-1 text-xs text-white/65">{{ formatPrice(item.price_minor) }} / {{ item.unit_label }}</p></div>
                                        <div class="flex items-center gap-1"><button type="button" class="grid size-8 place-items-center rounded-md border border-white/20 hover:bg-white/10" :aria-label="`Remove one ${item.name}`" @click="adjustQuantity(item.id, -1)"><Minus :size="14" aria-hidden="true" /></button><span class="w-6 text-center text-sm font-bold">{{ item.quantity }}</span><button type="button" class="grid size-8 place-items-center rounded-md border border-white/20 hover:bg-white/10" :aria-label="`Add one ${item.name}`" @click="adjustQuantity(item.id, 1)"><Plus :size="14" aria-hidden="true" /></button></div>
                                        <strong class="w-20 text-right font-mono text-xs">{{ formatPrice(item.price_minor * item.quantity) }}</strong>
                                        <button type="button" class="grid size-8 place-items-center rounded-md text-white/60 hover:bg-white/10 hover:text-white" :aria-label="`Remove ${item.name} from sale`" @click="cart = cart.filter((line) => line.id !== item.id)"><Trash2 :size="15" aria-hidden="true" /></button>
                                    </div>
                                </div>
                                <p v-else class="mt-4 rounded-md border border-dashed border-white/30 px-4 py-7 text-center text-sm text-white/70">Choose food from the menu to start a sale.</p>

                                <div class="mt-4 space-y-4 border-t border-white/20 pt-4 [&_input]:text-neutral-900 [&_select]:text-neutral-900">
                                    <div><div class="flex justify-between text-sm text-white/70"><span>Subtotal</span><span>{{ formatPrice(subtotalMinor) }}</span></div><label class="mt-3 block text-xs font-bold tracking-wide text-white/80 uppercase">Discount</label><div class="mt-1.5 grid grid-cols-[1fr_110px] gap-2"><select v-model="discountType" class="min-h-10 rounded-md border border-neutral-300 bg-white px-2 text-sm"><option value="">No discount</option><option value="fixed">Fixed amount</option><option value="percentage">Percentage</option></select><input v-model.number="discountValue" type="number" min="0" :max="discountType === 'percentage' ? 100 : undefined" step="0.01" placeholder="Amount" class="min-h-10 rounded-md border border-neutral-300 bg-white px-2 text-sm" /></div><input v-if="discountType" v-model="discountReason" type="text" maxlength="120" placeholder="Discount reason (optional)" class="mt-2 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm" /><div v-if="discountMinor > 0" class="mt-2 flex justify-between text-sm font-semibold text-emerald-100"><span>Discount</span><span>-{{ formatPrice(discountMinor) }}</span></div></div>
                                    <div class="flex justify-between border-t border-white/15 pt-3 text-lg font-black"><span>Total</span><span class="font-mono">{{ formatPrice(totalMinor) }}</span></div>
                                    <div class="space-y-3">
                                        <div v-for="(tender, index) in tenders" :key="tender.id" class="rounded-md border border-white/15 bg-white/5 p-3">
                                            <div class="flex items-center gap-2"><label class="sr-only" :for="`foodstore-tender-${tender.id}`">Payment method {{ index + 1 }}</label><select :id="`foodstore-tender-${tender.id}`" v-model="tender.method" class="min-h-10 min-w-0 flex-1 rounded-md border border-neutral-300 bg-white px-2 text-sm" @change="tender.externallyConfirmed = false; tender.cashReceivedGhs = null"><option value="cash">Cash</option><option value="mobile_money">Mobile Money terminal</option><option value="card">Card terminal</option></select><button v-if="tenders.length > 1" type="button" class="rounded p-2 text-white/60 hover:bg-white/10 hover:text-white" :aria-label="`Remove payment ${index + 1}`" @click="removeTender(tender.id)"><Trash2 :size="15" aria-hidden="true" /></button></div>
                                            <label class="mt-3 block text-xs font-bold tracking-wide text-white/75 uppercase">Amount applied (GHS)</label><input v-model.number="tender.amountGhs" type="number" min="0.01" step="0.01" inputmode="decimal" class="mt-1 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-right font-mono text-sm" />
                                            <template v-if="tender.method === 'cash'"><label class="mt-3 block text-xs font-bold tracking-wide text-white/75 uppercase">Cash received (GHS)</label><input v-model.number="tender.cashReceivedGhs" type="number" min="0.01" step="0.01" inputmode="decimal" placeholder="0.00" class="mt-1 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-right font-mono text-sm" /></template>
                                            <label v-else class="mt-3 flex items-start gap-2 text-xs font-semibold text-white/85"><input v-model="tender.externallyConfirmed" type="checkbox" class="mt-0.5 rounded border-neutral-300 text-emerald-700 focus:ring-emerald-600" />Payment received on external terminal</label>
                                        </div>
                                        <button type="button" class="inline-flex min-h-9 items-center gap-2 rounded-md border border-white/30 px-3 text-xs font-bold hover:bg-white/10 disabled:opacity-50" :disabled="tenders.length >= 3" @click="addTender"><Plus :size="14" aria-hidden="true" /> Add payment method</button>
                                        <p class="text-xs text-white/60">Split payment across cash, Card, and Mobile Money terminals.</p>
                                        <p v-if="!tenderTotalMatches" class="text-xs font-semibold text-amber-200">Balance due: {{ formatPrice(balanceDueMinor) }}<span v-if="balanceDueMinor === 0"> · Amounts exceed total by {{ formatPrice(appliedTenderTotalMinor - totalMinor) }}</span></p>
                                        <p v-if="!cashIsCovered && tenders.some((tender) => tender.method === 'cash')" class="text-xs font-semibold text-amber-200">Cash received must cover the cash amount applied.</p>
                                        <p v-if="!externalPaymentsConfirmed" class="text-xs font-semibold text-amber-200">Confirm each external payment before completing this sale.</p>
                                        <div v-if="cashAllocationMinor > 0 && cashIsCovered" class="flex justify-between rounded-md bg-emerald-300/15 px-3 py-2 text-sm font-bold text-emerald-100"><span>Change due</span><span class="font-mono">{{ formatPrice(changeMinor) }}</span></div>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2"><label class="block text-xs font-bold text-white/80">Customer (optional)<input v-model="customerName" maxlength="120" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm font-normal" /></label><label class="block text-xs font-bold text-white/80">Phone (optional)<input v-model="customerPhone" maxlength="40" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm font-normal" /></label></div>
                                    <p v-if="checkoutError" class="text-sm font-semibold text-red-200">{{ checkoutError }}</p>
                                    <button type="button" class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-emerald-300 px-4 text-sm font-black text-emerald-950 hover:bg-emerald-200 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!canCompleteSale || checkoutForm.processing" @click="placeSale">{{ checkoutForm.processing ? 'Processing sale...' : 'Complete sale' }}</button>
                                    <p v-if="!canCompleteSale && cart.length" class="text-center text-xs text-white/65">Match payment amounts to the balance, cover any cash payment, and confirm external payments.</p>
                                </div>
                            </div>
                        </section>
                    </aside>
                </div>
            </section>
        </div>
        <ReceiptPreview v-if="receipt" :open="receipt !== null" :tenant="tenant" :items="receipt.items" :total-minor="receipt.totalMinor" :payment-method="receipt.paymentMethod" :transaction-uuid="receipt.transactionUuid" :cashier-name="receipt.cashierName" :tenders="receipt.tenders" :customer-name="receipt.customerName" :customer-phone="receipt.customerPhone" :cash-received-minor="receipt.cashReceivedMinor" :change-minor="receipt.changeMinor" @close="receipt = null" />
    </main>
</template>