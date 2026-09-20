<script setup lang="ts">
import EnBadge from '@/Components/EnBadge.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import EnEmptyState from '@/Components/EnEmptyState.vue';
import BarcodeScanner from '@/Components/BarcodeScanner.vue';
import ProductArtwork from '@/Components/ProductArtwork.vue';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ChevronDown, LayoutGrid, List, LogOut, Menu, Pause, Play, Rows3, Trash2 } from '@lucide/vue';
import axios from 'axios';
import {
    markOfflineSaleSynced,
    pendingOfflineSaleCount,
    pendingOfflineSales,
    queueOfflineSale,
} from '@/lib/offlineDb';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

type Product = {
    id: number;
    name: string;
    sku: string;
    barcode?: string | null;
    price_minor: number;
    image_path?: string | null;
    inventory_stock?: { quantity: number };
};

type CartItem = Product & { quantity: number };
type HeldSale = {
    id: string;
    items: CartItem[];
    paymentMethod: 'cash' | 'mobile_money' | 'card';
    discountType: 'fixed' | 'percentage' | '';
    discountValue: number;
    discountReason: string;
};
type Receipt = {
    items: Array<{ name: string; quantity: number; priceMinor: number }>;
    totalMinor: number;
    paymentMethod: string;
    transactionUuid: string;
};

const props = defineProps<{
    products: Product[];
    tenant: string;
    cashierName: string;
}>();

const search = ref('');
const cart = ref<CartItem[]>([]);
const paymentMethod = ref<'cash' | 'mobile_money' | 'card'>('cash');
const discountType = ref<'fixed' | 'percentage' | ''>('');
const discountValue = ref(0);
const discountReason = ref('');
const mobilePanelOpen = ref(false);
const tenant = props.tenant;
const isOnline = ref(navigator.onLine);
const pendingCount = ref(0);
const syncInProgress = ref(false);
const scannerOpen = ref(false);
const receipt = ref<Receipt | null>(null);
const heldSales = ref<HeldSale[]>([]);
const displayMode = ref<'grid' | 'thumbnail' | 'list'>('grid');
const productPage = ref(1);
const logoutForm = useForm({});

const logout = (): void => {
    logoutForm.post(route('tenant.pos.logout', { tenant }));
};

const filteredProducts = computed(() => {
    const term = search.value.toLowerCase().trim();

    return props.products.filter(
        (product) =>
            term === '' ||
            product.name.toLowerCase().includes(term) ||
            product.sku.toLowerCase().includes(term),
    );
});

const paginatedProducts = computed(() => {
    const start = (productPage.value - 1) * 20;

    return filteredProducts.value.slice(start, start + 20);
});

const productPageCount = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / 20)));

watch(search, () => {
    productPage.value = 1;
});

const totalMinor = computed(() =>
    Math.max(0, subtotalMinor.value - discountMinor.value),
);

const subtotalMinor = computed(() =>
    cart.value.reduce(
        (total, item) => total + item.price_minor * item.quantity,
        0,
    ),
);

const discountMinor = computed(() => {
    if (discountType.value === 'percentage') {
        return Math.min(subtotalMinor.value, Math.round(subtotalMinor.value * Math.min(discountValue.value, 100) / 100));
    }

    return Math.min(subtotalMinor.value, Math.round(discountValue.value * 100));
});

const checkoutForm = useForm({
    transaction_uuid: crypto.randomUUID(),
    payment_method: paymentMethod.value,
    discount_type: null as 'fixed' | 'percentage' | null,
    discount_value: 0,
    discount_reason: '',
    items: [] as Array<{ product_id: number; quantity: number }>,
});

const addToCart = (product: Product): void => {
    const existing = cart.value.find((item) => item.id === product.id);

    if (existing) {
        existing.quantity += 1;
        return;
    }

    cart.value.push({ ...product, quantity: 1 });
};

const removeFromCart = (productId: number): void => {
    cart.value = cart.value.filter((item) => item.id !== productId);
};

const clearSale = (): void => {
    cart.value = [];
    paymentMethod.value = 'cash';
    discountType.value = '';
    discountValue.value = 0;
    discountReason.value = '';
};

const holdSale = (): void => {
    if (cart.value.length === 0) {
        return;
    }

    heldSales.value.push({
        id: crypto.randomUUID(),
        items: cart.value.map((item) => ({ ...item })),
        paymentMethod: paymentMethod.value,
        discountType: discountType.value,
        discountValue: discountValue.value,
        discountReason: discountReason.value,
    });
    clearSale();
};

const resumeSale = (heldSale: HeldSale): void => {
    if (cart.value.length > 0) {
        holdSale();
    }

    cart.value = heldSale.items.map((item) => ({ ...item }));
    paymentMethod.value = heldSale.paymentMethod;
    discountType.value = heldSale.discountType;
    discountValue.value = heldSale.discountValue;
    discountReason.value = heldSale.discountReason;
    heldSales.value = heldSales.value.filter((sale) => sale.id !== heldSale.id);
};

const handleBarcode = (code: string): void => {
    const normalizedCode = code.trim().toLowerCase();
    const product = props.products.find(
        (item) =>
            item.sku.toLowerCase() === normalizedCode ||
            item.barcode?.toLowerCase() === normalizedCode,
    );

    scannerOpen.value = false;

    if (product) {
        addToCart(product);
        search.value = '';
        return;
    }

    search.value = code.trim();
};

const formatPrice = (minor: number): string =>
    new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(minor / 100);

const checkout = (): void => {
    checkoutForm.payment_method = paymentMethod.value;
    checkoutForm.discount_type = discountType.value || null;
    checkoutForm.discount_value = discountValue.value;
    checkoutForm.discount_reason = discountReason.value;
    checkoutForm.items = cart.value.map((item) => ({
        product_id: item.id,
        quantity: item.quantity,
    }));
    const completedReceipt: Receipt = {
        items: cart.value.map((item) => ({
            name: item.name,
            quantity: item.quantity,
            priceMinor: item.price_minor,
        })),
        totalMinor: totalMinor.value,
        paymentMethod: paymentMethod.value,
        transactionUuid: checkoutForm.transaction_uuid,
    };

    if (!isOnline.value) {
        void queueOfflineSale({
            transactionUuid: checkoutForm.transaction_uuid,
            tenantId: tenant,
            items: cart.value.map((item) => ({
                productId: item.id,
                quantity: item.quantity,
                unitPriceMinor: item.price_minor,
            })),
            totalMinor: totalMinor.value,
            discountType: discountType.value || null,
            discountValue: discountValue.value,
            discountReason: discountReason.value,
            paymentMethod: paymentMethod.value,
            createdAt: new Date().toISOString(),
            synced: false,
        }).then(async () => {
            pendingCount.value = await pendingOfflineSaleCount();
            receipt.value = completedReceipt;
            clearSale();
            checkoutForm.transaction_uuid = crypto.randomUUID();
        });

        return;
    }

    checkoutForm.post(
        route('tenant.pos.checkout', { tenant: route().params.tenant }),
        {
            onSuccess: () => {
                receipt.value = completedReceipt;
                clearSale();
                checkoutForm.transaction_uuid = crypto.randomUUID();
            },
        },
    );
};

const refreshPendingCount = async (): Promise<void> => {
    pendingCount.value = await pendingOfflineSaleCount();
};

const syncOfflineSales = async (): Promise<void> => {
    if (!isOnline.value || syncInProgress.value) {
        return;
    }

    const sales = await pendingOfflineSales();

    if (sales.length === 0) {
        return;
    }

    syncInProgress.value = true;

    try {
        await axios.post(route('tenant.pos.sync', { tenant }), {
            sales: sales.map((sale) => ({
                transaction_uuid: sale.transactionUuid,
                payment_method: sale.paymentMethod,
                items: sale.items.map((item) => ({
                    product_id: item.productId,
                    quantity: item.quantity,
                })),
                discount_type: sale.discountType,
                discount_value: sale.discountValue,
                discount_reason: sale.discountReason,
            })),
        });

        await Promise.all(
            sales.map((sale) =>
                sale.id === undefined
                    ? Promise.resolve()
                    : markOfflineSaleSynced(sale.id),
            ),
        );
        await refreshPendingCount();
    } finally {
        syncInProgress.value = false;
    }
};

const handleOnline = (): void => {
    isOnline.value = true;
    void syncOfflineSales();
};

const handleOffline = (): void => {
    isOnline.value = false;
};

onMounted(() => {
    void refreshPendingCount();
    void syncOfflineSales();
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
});

onUnmounted(() => {
    window.removeEventListener('online', handleOnline);
    window.removeEventListener('offline', handleOffline);
});
</script>

<template>
    <Head title="Point of Sale">
        <link rel="manifest" :href="route('tenant.pos.manifest', { tenant })" />
    </Head>
    <main class="min-h-screen bg-neutral-50">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel
                v-if="$page.props.auth.user"
                :tenant="tenant"
                current="pos"
                :mobile-open="mobilePanelOpen"
                @close="mobilePanelOpen = false"
            />
            <div class="min-w-0 flex-1 px-4 py-6 sm:px-8">
                <button
                    type="button"
                    class="mb-5 inline-flex items-center gap-2 rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm font-medium text-neutral-700 shadow-sm lg:hidden"
                    aria-label="Open store navigation"
                    @click="mobilePanelOpen = true"
                >
                    <Menu :size="20" aria-hidden="true" />
                    Menu
                </button>
                <div class="mb-6 overflow-hidden rounded-2xl bg-linear-to-br from-[#3d0508] via-[#b3131b] to-[#ff4d55] text-white shadow-lg">
                    <div class="relative flex min-h-36 items-center justify-between gap-6 overflow-hidden px-6 py-6 sm:px-8">
                        <img src="/images/products/cart.svg" alt="" class="pointer-events-none absolute right-5 -bottom-18 w-48 opacity-15 brightness-0 invert" aria-hidden="true" />
                        <div class="relative">
                            <p class="text-xs font-bold tracking-[0.2em] text-emerald-400 uppercase">Enablstore POS</p>
                            <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Sell in-store</h1>
                        </div>
                        <details class="relative z-10 shrink-0">
                            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-sm font-semibold transition hover:bg-white/15">
                                <span>{{ cashierName }}</span>
                                <ChevronDown :size="16" aria-hidden="true" />
                            </summary>
                            <div class="absolute right-0 mt-2 w-40 rounded-lg border border-neutral-200 bg-white p-1 text-neutral-900 shadow-xl">
                                <form @submit.prevent="logout">
                                    <button type="submit" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-medium transition hover:bg-red-50 hover:text-red-700" :disabled="logoutForm.processing">
                                        <LogOut :size="16" aria-hidden="true" />
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px]">
                    <section class="min-w-0">
                        <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <EnBadge
                                    :tone="isOnline ? 'success' : 'warning'"
                                >
                                    {{ isOnline ? 'Online' : 'Offline' }}
                                </EnBadge>
                                <EnBadge v-if="pendingCount > 0" tone="warning">
                                    {{ pendingCount }} pending
                                </EnBadge>
                            </div>
                        </header>

                        <EnCard>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <input
                                    v-model="search"
                                    type="search"
                                    placeholder="Search products or scan a SKU"
                                    aria-label="Search products or scan a SKU"
                                    class="focus:border-primary-600 focus:ring-primary-100 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm focus:ring-2 focus:outline-none"
                                />
                                <EnButton variant="ghost" @click="scannerOpen = true">
                                    Scan barcode
                                </EnButton>
                            </div>
                        </EnCard>

                        <div v-if="filteredProducts.length" class="mt-6 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm text-neutral-500">Showing {{ (productPage - 1) * 20 + 1 }}-{{ Math.min(productPage * 20, filteredProducts.length) }} of {{ filteredProducts.length }} products</p>
                            <div class="flex items-center gap-1 rounded-md border border-neutral-200 bg-white p-1">
                                <button v-for="mode in [{ value: 'grid', label: 'Grid', icon: LayoutGrid }, { value: 'thumbnail', label: 'Thumbnail', icon: Rows3 }, { value: 'list', label: 'List', icon: List }]" :key="mode.value" type="button" class="rounded p-2" :class="displayMode === mode.value ? 'bg-red-50 text-red-700' : 'text-neutral-500 hover:bg-neutral-50'" :aria-label="`${mode.label} view`" @click="displayMode = mode.value as 'grid' | 'thumbnail' | 'list'">
                                    <component :is="mode.icon" :size="16" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                        <div v-if="filteredProducts.length" class="mt-4" :class="displayMode === 'grid' ? 'grid gap-4 sm:grid-cols-2 xl:grid-cols-3' : displayMode === 'thumbnail' ? 'grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-5' : 'space-y-2'">
                            <button
                                v-for="product in paginatedProducts"
                                :key="product.id"
                                type="button"
                                class="group hover:border-primary-300 rounded-lg border border-neutral-100 bg-white p-3 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50"
                                :class="displayMode === 'list' ? 'flex items-center gap-4' : ''"
                                :disabled="
                                    (product.inventory_stock?.quantity ?? 0) < 1
                                "
                                @click="addToCart(product)"
                            >
                                <ProductArtwork
                                    :name="product.name"
                                    :sku="product.sku"
                                    :image-path="product.image_path"
                                    :size="displayMode === 'grid' ? 'large' : 'compact'"
                                />
                                <div class="p-2" :class="displayMode === 'list' ? 'flex flex-1 items-center justify-between gap-4' : ''">
                                    <p class="font-semibold text-neutral-900">
                                        {{ product.name }}
                                    </p>
                                    <p
                                        class="mt-1 font-mono text-xs text-neutral-500"
                                    >
                                        {{ product.sku }}
                                    </p>
                                    <div
                                        class="mt-6 flex items-end justify-between gap-3"
                                    >
                                        <span
                                            class="text-primary-700 font-mono text-sm"
                                            >{{
                                                formatPrice(product.price_minor)
                                            }}</span
                                        >
                                        <span class="text-xs text-neutral-500"
                                            >{{
                                                product.inventory_stock
                                                    ?.quantity ?? 0
                                            }}
                                            left</span
                                        >
                                    </div>
                                </div>
                            </button>
                        </div>
                        <div v-if="filteredProducts.length > 20" class="mt-5 flex items-center justify-center gap-3">
                            <button type="button" class="rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm disabled:opacity-40" :disabled="productPage === 1" @click="productPage--">Previous</button>
                            <span class="text-sm text-neutral-500">Page {{ productPage }} of {{ productPageCount }}</span>
                            <button type="button" class="rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm disabled:opacity-40" :disabled="productPage === productPageCount" @click="productPage++">Next</button>
                        </div>
                        <EnCard v-else class="mt-6">
                            <EnEmptyState
                                title="No matching products"
                                description="Try another search or add products to your catalogue."
                            />
                        </EnCard>
                    </section>

                    <aside class="lg:sticky lg:top-6 lg:self-start">
                        <EnCard class="border-white/10! bg-[#202020]! text-white [&_h2]:text-white [&_p]:text-white/75">
                            <div class="flex items-center justify-between">
                                <h2
                                    class="text-xl font-semibold text-white"
                                >
                                    Current sale
                                </h2>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm text-white/70">{{ cart.length }} items</span>
                                    <button type="button" class="inline-flex items-center gap-1 rounded-md border border-white/35 bg-white/10 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-white/20 disabled:cursor-not-allowed disabled:opacity-40" title="Hold current sale and start another" :disabled="cart.length === 0" @click="holdSale">
                                        <Pause :size="14" aria-hidden="true" />
                                        Hold sale
                                    </button>
                                </div>
                            </div>
                            <div v-if="heldSales.length" class="mt-4 rounded-lg border border-white/15 bg-black/15 p-3">
                                <p class="text-xs font-bold tracking-wide text-white/70 uppercase">Held sales</p>
                                <div class="mt-2 space-y-2">
                                    <button v-for="(heldSale, index) in heldSales" :key="heldSale.id" type="button" class="flex w-full items-center justify-between rounded-md bg-white/10 px-3 py-2 text-left text-sm text-white transition hover:bg-white/20" @click="resumeSale(heldSale)">
                                        <span>Sale {{ index + 1 }} · {{ heldSale.items.length }} items</span>
                                        <Play :size="15" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                            <div v-if="cart.length" class="mt-6 space-y-4">
                                <div
                                    v-for="item in cart"
                                    :key="item.id"
                                    class="flex justify-between gap-4"
                                >
                                    <div>
                                        <p class="font-medium text-white">
                                            {{ item.name }}
                                        </p>
                                        <p class="text-sm text-white/70">
                                            {{ item.quantity }} x
                                            {{ formatPrice(item.price_minor) }}
                                        </p>
                                    </div>
                                    <p
                                        class="font-mono text-sm text-white/90"
                                    >
                                        {{
                                            formatPrice(
                                                item.price_minor *
                                                    item.quantity,
                                            )
                                        }}
                                    </p>
                                    <button type="button" class="shrink-0 rounded-md p-1 text-white/60 transition hover:bg-white/10 hover:text-white" title="Remove item" aria-label="Remove item" @click="removeFromCart(item.id)">
                                        <Trash2 :size="16" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                            <EnEmptyState
                                v-else
                                title="Cart is empty"
                                description="Select a product to start the sale."
                            />
                            <div class="mt-6 border-t border-white/20 pt-5 [&_input]:text-white [&_input]:placeholder:text-white/60 [&_select]:text-neutral-900">
                                                <div class="flex justify-between text-sm text-white/70"><span>Subtotal</span><span>{{ formatPrice(subtotalMinor) }}</span></div>
                                                <div class="mt-3 grid grid-cols-[1fr_110px] gap-2"><select v-model="discountType" class="min-h-10 rounded-md border border-neutral-300 bg-white px-2 text-sm"><option value="">No discount</option><option value="fixed">Fixed discount</option><option value="percentage">Percentage discount</option></select><input v-model.number="discountValue" type="number" min="0" :max="discountType === 'percentage' ? 100 : undefined" step="0.01" placeholder="Amount" class="min-h-10 rounded-md border border-neutral-300 px-2 text-sm" /></div>
                                                <input v-if="discountType" v-model="discountReason" type="text" maxlength="120" placeholder="Discount reason (optional)" class="mt-2 min-h-10 w-full rounded-md border border-neutral-300 px-3 text-sm" />
                                                <div v-if="discountMinor > 0" class="mt-3 flex justify-between text-sm font-semibold text-white"><span>Discount</span><span>-{{ formatPrice(discountMinor) }}</span></div>
                                <div
                                    class="flex justify-between text-lg font-bold text-white"
                                >
                                    <span>Total</span>
                                    <span class="font-mono">{{
                                        formatPrice(totalMinor)
                                    }}</span>
                                </div>
                                <select
                                    v-model="paymentMethod"
                                    class="mt-4 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm text-neutral-900"
                                    aria-label="Payment method"
                                >
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">
                                        Mobile Money
                                    </option>
                                    <option value="card">Card</option>
                                </select>
                                <EnButton
                                    class="mt-4 w-full bg-blue-600! text-white! hover:bg-blue-700!"
                                    variant="primary"
                                    :loading="checkoutForm.processing"
                                    :disabled="cart.length === 0"
                                    @click="checkout"
                                >
                                    Complete sale
                                </EnButton>
                            </div>
                        </EnCard>
                    </aside>
                </div>
            </div>
        </div>
        <ReceiptPreview
            v-if="receipt"
            :open="receipt !== null"
            :tenant="tenant"
            :items="receipt.items"
            :total-minor="receipt.totalMinor"
            :payment-method="receipt.paymentMethod"
            :transaction-uuid="receipt.transactionUuid"
            @close="receipt = null"
        />
        <BarcodeScanner :open="scannerOpen" @close="scannerOpen = false" @detected="handleBarcode" />
    </main>
</template>
