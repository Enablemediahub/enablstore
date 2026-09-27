<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnCard from '@/Components/EnCard.vue';
import EnEmptyState from '@/Components/EnEmptyState.vue';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LayoutDashboard, Minus, Plus, ShoppingCart, Trash2, Utensils } from '@lucide/vue';
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
    cashierName: string | null;
    menuItems: MenuItem[];
    completedReceipt?: Receipt | null;
    status?: string | null;
}>();

const adminPortalOpen = ref(false);
const search = ref('');
const cart = ref<CartItem[]>([]);
const paymentMethod = ref<TenderMethod>('cash');
const cashReceivedGhs = ref<number | null>(null);
const paymentConfirmed = ref(false);
const customerName = ref('');
const customerPhone = ref('');
const receipt = ref<Receipt | null>(props.completedReceipt ?? null);
const checkoutForm = useForm({
    transaction_uuid: crypto.randomUUID(),
    payment_method: 'cash' as TenderMethod,
    customer_name: '',
    customer_phone: '',
    items: [] as Array<{ menu_item_id: number; quantity: number }>,
    tenders: [] as Array<{ method: TenderMethod; amount_minor: number; cash_received_minor: number | null; externally_confirmed: boolean }>,
});
const logoutForm = useForm({});
const checkoutError = computed(() => (checkoutForm.errors as Record<string, string>).checkout);

const filteredMenu = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.menuItems.filter((item) => `${item.name} ${item.category}`.toLowerCase().includes(term));
});
const totalMinor = computed(() => cart.value.reduce((total, item) => total + item.price_minor * item.quantity, 0));
const cashReceivedMinor = computed(() => Math.round(Number(cashReceivedGhs.value ?? 0) * 100));
const changeMinor = computed(() => Math.max(0, cashReceivedMinor.value - totalMinor.value));
const canCompleteSale = computed(() => cart.value.length > 0 && totalMinor.value > 0 && (
    paymentMethod.value === 'cash' ? cashReceivedMinor.value >= totalMinor.value : paymentConfirmed.value
));

watch(paymentMethod, () => {
    paymentConfirmed.value = false;
    if (paymentMethod.value === 'cash') cashReceivedGhs.value = null;
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

const placeSale = (): void => {
    if (!canCompleteSale.value) return;

    checkoutForm.payment_method = paymentMethod.value;
    checkoutForm.customer_name = customerName.value;
    checkoutForm.customer_phone = customerPhone.value;
    checkoutForm.items = cart.value.map((item) => ({ menu_item_id: item.id, quantity: item.quantity }));
    checkoutForm.tenders = [{
        method: paymentMethod.value,
        amount_minor: totalMinor.value,
        cash_received_minor: paymentMethod.value === 'cash' ? cashReceivedMinor.value : null,
        externally_confirmed: paymentMethod.value !== 'cash' && paymentConfirmed.value,
    }];
    checkoutForm.post(route('tenant.foodstore.sales.store', { tenant: props.tenant }), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = [];
            cashReceivedGhs.value = null;
            paymentMethod.value = 'cash';
            paymentConfirmed.value = false;
            customerName.value = '';
            customerPhone.value = '';
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
                <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold tracking-[0.2em] text-emerald-800 uppercase">{{ restaurantName }}</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight">FoodStore</h1>
                        <p class="mt-2 text-sm text-neutral-600">Select food, build the cart, and complete the sale.</p>
                    </div>
                    <div class="flex items-center gap-3"><div class="flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-900"><Utensils :size="17" aria-hidden="true" /> Food sales</div><button v-if="$page.props.auth.user?.role === 'admin'" type="button" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-emerald-200 bg-white px-3 text-sm font-semibold text-emerald-900 hover:bg-emerald-50" @click="adminPortalOpen = !adminPortalOpen"><LayoutDashboard :size="16" aria-hidden="true" />{{ adminPortalOpen ? 'Close Admin Portal' : 'Admin Portal' }}</button><button v-if="cashierName" type="button" class="min-h-10 rounded-md border border-neutral-300 bg-white px-3 text-sm font-semibold text-neutral-700 hover:border-red-300 hover:text-red-700" :disabled="logoutForm.processing" @click="logout">Sign out {{ cashierName }}</button></div>
                </header>
                <p v-if="status" class="mb-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ status }}</p>

                <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_390px]">
                    <section class="min-w-0 space-y-5">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div><p class="text-xs font-bold tracking-[0.18em] text-emerald-800 uppercase">Menu</p><h2 class="mt-1 text-xl font-black">Available food</h2></div>
                            <input v-model="search" type="search" class="min-h-10 w-full max-w-xs rounded-md border border-neutral-300 bg-white px-3 text-sm" placeholder="Find food" aria-label="Find food" />
                        </div>
                        <div v-if="filteredMenu.length" class="grid gap-3 sm:grid-cols-2 2xl:grid-cols-3">
                            <article v-for="item in filteredMenu" :key="item.id" class="overflow-hidden rounded-md border border-neutral-200 bg-white">
                                <button type="button" class="flex w-full flex-col items-start p-4 text-left transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!item.is_available" @click="addToCart(item)">
                                    <div class="mb-3 aspect-[4/3] w-full overflow-hidden rounded-md bg-emerald-50">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                                        <div v-else class="grid h-full place-items-center text-emerald-700"><Utensils :size="32" aria-hidden="true" /></div>
                                    </div>
                                    <span class="text-xs font-bold tracking-wide text-emerald-800 uppercase">{{ item.category }}</span>
                                    <span class="mt-3 text-base font-bold text-neutral-900">{{ item.name }}</span>
                                    <span v-if="item.description" class="mt-1 line-clamp-2 text-xs text-neutral-500">{{ item.description }}</span>
                                    <span class="mt-4 flex w-full items-center justify-between gap-2"><strong class="font-mono text-sm">{{ formatPrice(item.price_minor) }} <span class="font-sans font-normal text-neutral-500">/ {{ item.unit_label }}</span></strong><span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800"><Plus :size="15" aria-hidden="true" /> Add</span></span>
                                </button>
                            </article>
                        </div>
                        <EnEmptyState v-else title="No food items found" description="Add menu items or change the search." />
                    </section>

                    <aside class="xl:sticky xl:top-5">
                        <EnCard>
                            <div class="flex items-center justify-between"><div class="flex items-center gap-2"><ShoppingCart :size="19" class="text-emerald-800" aria-hidden="true" /><h2 class="font-bold">Current sale</h2></div><span class="text-xs text-neutral-500">{{ cart.reduce((count, item) => count + item.quantity, 0) }} items</span></div>
                            <div v-if="cart.length" class="mt-4 divide-y divide-neutral-100">
                                <div v-for="item in cart" :key="item.id" class="flex items-center gap-3 py-3">
                                    <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ item.name }}</p><p class="mt-1 text-xs text-neutral-500">{{ formatPrice(item.price_minor) }} / {{ item.unit_label }}</p></div>
                                    <div class="flex items-center gap-1"><button type="button" class="grid size-8 place-items-center rounded-md border border-neutral-200 hover:bg-neutral-50" :aria-label="`Remove one ${item.name}`" @click="adjustQuantity(item.id, -1)"><Minus :size="14" aria-hidden="true" /></button><span class="w-7 text-center text-sm font-bold">{{ item.quantity }}</span><button type="button" class="grid size-8 place-items-center rounded-md border border-neutral-200 hover:bg-neutral-50" :aria-label="`Add one ${item.name}`" @click="adjustQuantity(item.id, 1)"><Plus :size="14" aria-hidden="true" /></button></div>
                                    <strong class="w-20 text-right font-mono text-xs">{{ formatPrice(item.price_minor * item.quantity) }}</strong>
                                    <button type="button" class="grid size-8 place-items-center rounded-md text-neutral-400 hover:bg-red-50 hover:text-red-700" :aria-label="`Remove ${item.name} from sale`" @click="cart = cart.filter((line) => line.id !== item.id)"><Trash2 :size="15" aria-hidden="true" /></button>
                                </div>
                            </div>
                            <p v-else class="mt-4 rounded-md border border-dashed border-neutral-300 px-4 py-8 text-center text-sm text-neutral-500">Choose food from the menu to start a sale.</p>

                            <div class="mt-4 border-t border-neutral-200 pt-4">
                                <div class="flex justify-between text-lg font-black"><span>Total</span><span class="font-mono">{{ formatPrice(totalMinor) }}</span></div>
                                <label class="mt-4 block text-sm font-medium text-neutral-700">Payment method<select v-model="paymentMethod" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="cash">Cash</option><option value="mobile_money">Mobile Money terminal</option><option value="card">Card terminal</option></select></label>
                                <label v-if="paymentMethod === 'cash'" class="mt-3 block text-sm font-medium text-neutral-700">Cash received (GHS)<input v-model.number="cashReceivedGhs" type="number" min="0" step="0.01" inputmode="decimal" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 px-3 text-right font-mono text-sm" /></label>
                                <label v-else class="mt-3 flex items-start gap-2 text-sm text-neutral-700"><input v-model="paymentConfirmed" type="checkbox" class="mt-0.5 rounded border-neutral-300 text-emerald-700 focus:ring-emerald-600" />Payment received on external terminal</label>
                                <div v-if="paymentMethod === 'cash' && changeMinor > 0" class="mt-3 flex justify-between rounded-md bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-900"><span>Change due</span><span class="font-mono">{{ formatPrice(changeMinor) }}</span></div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2"><label class="block text-sm font-medium text-neutral-700">Customer (optional)<input v-model="customerName" maxlength="120" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label><label class="block text-sm font-medium text-neutral-700">Phone (optional)<input v-model="customerPhone" maxlength="40" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label></div>
                                <p v-if="checkoutError" class="mt-3 text-sm font-semibold text-red-700">{{ checkoutError }}</p>
                                <button type="button" class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-emerald-700 px-4 text-sm font-bold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!canCompleteSale || checkoutForm.processing" @click="placeSale">{{ checkoutForm.processing ? 'Processing sale...' : 'Complete sale' }}</button>
                                <p v-if="!canCompleteSale && cart.length" class="mt-2 text-center text-xs text-neutral-500">{{ paymentMethod === 'cash' ? 'Enter enough cash to cover the total.' : 'Confirm payment on the external terminal to continue.' }}</p>
                            </div>
                        </EnCard>
                    </aside>
                </div>
            </section>
        </div>
        <ReceiptPreview v-if="receipt" :open="receipt !== null" :tenant="tenant" :items="receipt.items" :total-minor="receipt.totalMinor" :payment-method="receipt.paymentMethod" :transaction-uuid="receipt.transactionUuid" :cashier-name="receipt.cashierName" :tenders="receipt.tenders" :customer-name="receipt.customerName" :customer-phone="receipt.customerPhone" :cash-received-minor="receipt.cashReceivedMinor" :change-minor="receipt.changeMinor" @close="receipt = null" />
    </main>
</template>