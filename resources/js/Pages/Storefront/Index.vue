<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import EnEmptyState from '@/Components/EnEmptyState.vue';
import Modal from '@/Components/Modal.vue';
import ProductArtwork from '@/Components/ProductArtwork.vue';
import { Head } from '@inertiajs/vue3';
import {
    ChevronRight,
    Mail,
    MapPin,
    Minus,
    Phone,
    Plus,
    Search,
    ShoppingCart,
    UserRound,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';

type Product = {
    id: number;
    name: string;
    sku: string;
    price_minor: number;
    compare_at_price_minor?: number | null;
    is_online_deal?: boolean;
    category_id?: number | null;
    category_name?: string | null;
    total_sold?: number;
    created_at?: string | null;
    image_path?: string | null;
    image_gallery?: string[] | null;
};

type CartItem = Product & { quantity: number };

type StorefrontHero = {
    title: string;
    subtitle: string;
    badge: string;
    imageUrl: string;
};

type StorefrontConfig = {
    storeName: string;
    deliveryLocations: string[];
    defaultDeliveryLocation: string;
    deliveryMessage: string;
    heroDeliveryMessage: string;
    newArrivalsDays: number;
    customerService: {
        title: string;
        phone: string;
        email: string;
        hours: string;
        message: string;
    };
};

type Category = { id: number; name: string };

type NavFilter = 'all' | 'deals' | 'best_sellers' | 'new_arrivals';

const props = defineProps<{
    hero: StorefrontHero;
    logoUrl: string;
    storefront: StorefrontConfig;
    categories: Category[];
    products: Product[];
}>();

const productsSection = ref<HTMLElement | null>(null);
const basketAside = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);

const search = ref('');
const cart = ref<CartItem[]>([]);
const tenant = String(route().params.tenant);
const selectedCategory = ref<string>('All');
const selectedProduct = ref<Product | null>(null);
const selectedAngle = ref(0);
const activeNav = ref<NavFilter>('all');
const deliveryLocation = ref(props.storefront.defaultDeliveryLocation);
const locationModalOpen = ref(false);
const customerServiceOpen = ref(false);
const accountModalOpen = ref(false);

const deliveryStorageKey = `enablstore-delivery-${tenant}`;

onMounted(() => {
    const saved = localStorage.getItem(deliveryStorageKey);

    if (saved && props.storefront.deliveryLocations.includes(saved)) {
        deliveryLocation.value = saved;
    }
});

const categoryOptions = computed(() => ['All', ...props.categories.map((category) => category.name)]);

const isDeal = (product: Product): boolean =>
    Boolean(product.is_online_deal) ||
    (product.compare_at_price_minor != null && product.compare_at_price_minor > product.price_minor);

const isNewArrival = (product: Product): boolean => {
    if (!product.created_at) {
        return false;
    }

    const cutoff = Date.now() - props.storefront.newArrivalsDays * 86_400_000;

    return new Date(product.created_at).getTime() >= cutoff;
};

const navMatches = (product: Product): boolean => {
    if (activeNav.value === 'deals') {
        return isDeal(product);
    }

    if (activeNav.value === 'new_arrivals') {
        return isNewArrival(product);
    }

    return true;
};

const filteredProducts = computed(() => {
    const term = search.value.trim().toLowerCase();

    let results = props.products.filter((product) => {
        const name = product.name.toLowerCase();
        const matchesSearch = term === '' || name.includes(term);
        const matchesCategory =
            selectedCategory.value === 'All' || product.category_name === selectedCategory.value;

        return matchesSearch && matchesCategory && navMatches(product);
    });

    if (activeNav.value === 'best_sellers') {
        results = [...results].sort((left, right) => (right.total_sold ?? 0) - (left.total_sold ?? 0));
    }

    return results;
});

const sectionLabel = computed(() => {
    if (activeNav.value === 'deals') {
        return "Today's deals";
    }

    if (activeNav.value === 'best_sellers') {
        return 'Best sellers';
    }

    if (activeNav.value === 'new_arrivals') {
        return 'New arrivals';
    }

    return 'Featured selection';
});

const sectionTitle = computed(() => {
    if (activeNav.value === 'deals') {
        return 'Save on selected products';
    }

    if (activeNav.value === 'best_sellers') {
        return 'Most popular right now';
    }

    if (activeNav.value === 'new_arrivals') {
        return 'Fresh additions to the store';
    }

    return 'Shop popular products';
});

const scrollToProducts = (): void => {
    productsSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const focusSearch = (): void => {
    scrollToProducts();
    searchInput.value?.focus();
};

const scrollToBasket = (): void => {
    basketAside.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const selectLocation = (location: string): void => {
    deliveryLocation.value = location;
    localStorage.setItem(deliveryStorageKey, location);
    locationModalOpen.value = false;
};

const setNav = (filter: NavFilter): void => {
    activeNav.value = filter;
    scrollToProducts();
};

const openCustomerService = (): void => {
    customerServiceOpen.value = true;
};

const totalMinor = computed(() =>
    cart.value.reduce((total, item) => total + item.price_minor * item.quantity, 0),
);

const addToCart = (product: Product): void => {
    const existing = cart.value.find((item) => item.id === product.id);

    if (existing) {
        existing.quantity += 1;
        return;
    }

    cart.value.push({ ...product, quantity: 1 });
};

const productImages = (product: Product): string[] => {
    const paths = [product.image_path, ...(product.image_gallery ?? [])].filter(
        (path): path is string => Boolean(path),
    );

    return paths.map((path) => {
        const normalized = path.replace(/^\//, '');

        if (normalized.startsWith('storage/')) {
            return `/client/${tenant}/media/${normalized.replace(/^storage\//, '')}`;
        }

        return `/${normalized}`;
    });
};

const openProduct = (product: Product): void => {
    selectedProduct.value = product;
    selectedAngle.value = 0;
};

const changeQuantity = (productId: number, amount: number): void => {
    const item = cart.value.find((cartItem) => cartItem.id === productId);

    if (!item) {
        return;
    }

    item.quantity += amount;

    if (item.quantity <= 0) {
        cart.value = cart.value.filter((cartItem) => cartItem.id !== productId);
    }
};

const formatPrice = (minor: number): string =>
    new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(minor / 100);
</script>

<template>
    <Head :title="storefront.storeName" />
    <main class="min-h-screen bg-[#f4f4f2] text-[#171717]">
        <header class="bg-[#171717] text-white">
            <div class="mx-auto flex max-w-[1500px] items-center gap-5 px-4 py-3 sm:px-8">
                <a :href="route('tenant.home', { tenant })" class="shrink-0">
                    <img
                        :src="logoUrl"
                        :alt="storefront.storeName"
                        class="h-10 w-auto max-w-[180px] object-contain sm:h-11"
                    />
                </a>
                <button
                    type="button"
                    class="hidden items-center gap-2 text-left text-xs leading-tight transition hover:text-[#ff9696] md:flex"
                    @click="locationModalOpen = true"
                >
                    <MapPin :size="19" class="text-white" aria-hidden="true" />
                    <span>
                        <span class="text-neutral-300">Deliver to</span>
                        <strong class="block">{{ deliveryLocation }}</strong>
                    </span>
                </button>
                <label class="flex min-w-0 flex-1 overflow-hidden rounded-md bg-white focus-within:ring-2 focus-within:ring-[#e21b23]">
                    <span class="sr-only">Search products</span>
                    <input
                        ref="searchInput"
                        v-model="search"
                        type="search"
                        :placeholder="`Search ${storefront.storeName}`"
                        aria-label="Search products"
                        class="min-h-11 min-w-0 flex-1 border-0 bg-transparent px-4 text-sm text-[#172337] outline-none placeholder:text-neutral-500"
                    />
                    <button
                        type="button"
                        class="grid w-12 shrink-0 place-items-center bg-[#e21b23] text-white hover:bg-[#b9151b]"
                        aria-label="Search"
                        @click="focusSearch"
                    >
                        <Search :size="21" aria-hidden="true" />
                    </button>
                </label>
                <button
                    type="button"
                    class="hidden items-center gap-2 text-left text-xs transition hover:text-[#ff9696] sm:flex"
                    @click="accountModalOpen = true"
                >
                    <UserRound :size="20" aria-hidden="true" />
                    <span>Hello, shopper<br /><strong class="text-sm">Account &amp; Lists</strong></span>
                </button>
                <button
                    type="button"
                    class="flex items-end gap-1 text-xs font-bold transition hover:text-[#ff9696]"
                    aria-label="View basket"
                    @click="scrollToBasket"
                >
                    <ShoppingCart :size="28" aria-hidden="true" />
                    <span>{{ cart.length }}</span>
                </button>
            </div>
            <nav class="bg-[#2b2b2b]">
                <div class="mx-auto flex max-w-[1500px] items-center gap-6 overflow-x-auto px-4 py-2 text-sm font-semibold whitespace-nowrap sm:px-8">
                    <button
                        type="button"
                        class="transition hover:text-[#ff9696]"
                        :class="activeNav === 'deals' ? 'text-[#ff9696]' : 'text-white'"
                        @click="setNav('deals')"
                    >
                        Today's Deals
                    </button>
                    <button
                        type="button"
                        class="transition hover:text-[#ff9696]"
                        :class="activeNav === 'best_sellers' ? 'text-[#ff9696]' : 'text-white'"
                        @click="setNav('best_sellers')"
                    >
                        Best Sellers
                    </button>
                    <button
                        type="button"
                        class="transition hover:text-[#ff9696]"
                        :class="activeNav === 'new_arrivals' ? 'text-[#ff9696]' : 'text-white'"
                        @click="setNav('new_arrivals')"
                    >
                        New Arrivals
                    </button>
                    <button type="button" class="text-white transition hover:text-[#ff9696]" @click="openCustomerService">
                        Customer Service
                    </button>
                    <button
                        type="button"
                        class="ml-auto hidden text-neutral-300 transition hover:text-white lg:block"
                        @click="locationModalOpen = true"
                    >
                        {{ storefront.deliveryMessage }}
                    </button>
                </div>
            </nav>
        </header>

        <div class="mx-auto max-w-[1500px] px-4 py-5 sm:px-8">
            <section class="relative overflow-hidden rounded-2xl shadow-lg sm:rounded-3xl">
                <div class="absolute inset-0">
                    <img
                        :src="hero.imageUrl"
                        alt=""
                        class="h-full w-full scale-105 object-cover transition duration-700 hover:scale-100"
                    />
                    <div class="absolute inset-0 bg-gradient-to-r from-[#171717]/90 via-[#171717]/65 to-[#171717]/25" />
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(226,27,35,0.35),transparent_55%)]" />
                </div>
                <div class="relative flex min-h-[280px] flex-col justify-center px-6 py-10 sm:min-h-[340px] sm:px-12 sm:py-14 lg:min-h-[380px]">
                    <span class="inline-flex w-fit items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold tracking-wide text-white uppercase backdrop-blur-sm">
                        {{ hero.badge }}
                    </span>
                    <h1 class="mt-4 max-w-3xl text-3xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">
                        {{ hero.title }}
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-white/85 sm:text-base">
                        {{ hero.subtitle }}
                    </p>
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#e21b23] px-6 text-sm font-bold text-white shadow-lg shadow-[#e21b23]/30 transition hover:bg-[#b9151b]"
                            @click="scrollToProducts"
                        >
                            Shop now
                        </button>
                        <span class="text-sm font-medium text-white/75">{{ storefront.heroDeliveryMessage }}</span>
                    </div>
                </div>
            </section>

            <div class="mt-5 flex items-center gap-3 overflow-x-auto border-b border-neutral-300 pb-3">
                <button
                    v-for="category in categoryOptions"
                    :key="category"
                    type="button"
                    class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition"
                    :class="selectedCategory === category ? 'border-[#e21b23] bg-[#e21b23] text-white' : 'border-neutral-300 bg-white text-[#555555] hover:border-[#e21b23]'"
                    @click="selectedCategory = category; activeNav = 'all'"
                >
                    {{ category }}
                </button>
            </div>

            <div ref="productsSection" class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section>
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold tracking-widest text-[#b9151b] uppercase">{{ sectionLabel }}</p>
                            <h2 class="mt-1 text-2xl font-black text-[#171717]">{{ sectionTitle }}</h2>
                        </div>
                        <button
                            v-if="activeNav !== 'all'"
                            type="button"
                            class="text-sm font-semibold text-[#b9151b] hover:underline"
                            @click="activeNav = 'all'"
                        >
                            Show all
                        </button>
                        <span v-else class="text-sm text-[#40536b]">{{ filteredProducts.length }} items</span>
                    </div>
                    <div v-if="filteredProducts.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <article
                            v-for="product in filteredProducts"
                            :key="product.id"
                            class="group flex cursor-pointer flex-col rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#e21b23] hover:shadow-lg"
                            @click="openProduct(product)"
                        >
                            <ProductArtwork :name="product.name" :sku="product.sku" :image-path="product.image_path" size="large" />
                            <div class="flex flex-1 flex-col pt-4">
                                <p class="text-xs text-neutral-500">
                                    <span v-if="isDeal(product)" class="font-bold text-[#e21b23]">Deal</span>
                                    <span v-else-if="isNewArrival(product)" class="font-bold text-[#40536b]">New</span>
                                    <span v-else>{{ product.category_name ?? storefront.storeName }}</span>
                                </p>
                                <h3 class="mt-1 line-clamp-2 min-h-12 text-base font-semibold text-[#171717]">{{ product.name }}</h3>
                                <div class="mt-3 flex flex-wrap items-baseline gap-2">
                                    <span class="text-xl font-black text-[#171717]">{{ formatPrice(product.price_minor) }}</span>
                                    <span
                                        v-if="product.compare_at_price_minor && product.compare_at_price_minor > product.price_minor"
                                        class="text-sm text-neutral-400 line-through"
                                    >
                                        {{ formatPrice(product.compare_at_price_minor) }}
                                    </span>
                                    <span class="text-xs text-neutral-500">in stock</span>
                                </div>
                                <button
                                    type="button"
                                    class="mt-4 min-h-10 rounded-full bg-[#e21b23] px-4 text-sm font-bold text-white transition hover:bg-[#b9151b]"
                                    @click.stop="addToCart(product)"
                                >
                                    Add to cart
                                </button>
                            </div>
                        </article>
                    </div>
                    <EnCard v-else>
                        <EnEmptyState
                            title="No products found"
                            :description="activeNav === 'deals' ? 'No deals are available right now. Mark products as online deals in admin, or add a compare-at price.' : 'Try another search, category, or browse all products.'"
                        />
                    </EnCard>
                </section>

                <aside ref="basketAside" class="lg:sticky lg:top-5 lg:self-start">
                    <div class="border border-neutral-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-black text-[#171717]">Your basket</h2>
                            <ShoppingCart :size="22" class="text-[#e21b23]" aria-hidden="true" />
                        </div>
                        <p v-if="deliveryLocation" class="mt-2 text-xs text-neutral-500">Delivering to {{ deliveryLocation }}</p>
                        <div v-if="cart.length" class="mt-5 space-y-4">
                            <div v-for="item in cart" :key="item.id" class="border-b border-neutral-100 pb-4">
                                <div class="flex justify-between gap-3">
                                    <p class="text-sm font-semibold text-[#171717]">{{ item.name }}</p>
                                    <span class="shrink-0 text-sm font-bold">{{ formatPrice(item.price_minor * item.quantity) }}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <div class="flex items-center rounded-full border border-neutral-300">
                                        <button type="button" class="grid size-8 place-items-center text-[#555555] hover:text-[#e21b23]" :aria-label="`Remove one ${item.name}`" @click="changeQuantity(item.id, -1)"><Minus :size="15" aria-hidden="true" /></button>
                                        <span class="w-7 text-center text-sm font-bold">{{ item.quantity }}</span>
                                        <button type="button" class="grid size-8 place-items-center text-[#555555] hover:text-[#e21b23]" :aria-label="`Add one ${item.name}`" @click="changeQuantity(item.id, 1)"><Plus :size="15" aria-hidden="true" /></button>
                                    </div>
                                    <span class="text-xs text-neutral-500">{{ formatPrice(item.price_minor) }} each</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 text-lg font-black"><span>Subtotal</span><span>{{ formatPrice(totalMinor) }}</span></div>
                            <button type="button" class="flex min-h-11 w-full items-center justify-center gap-2 rounded-full bg-[#e21b23] px-4 text-sm font-bold text-white hover:bg-[#b9151b]"><span>Proceed to checkout</span><ChevronRight :size="18" aria-hidden="true" /></button>
                        </div>
                        <div v-else class="py-8 text-center">
                            <ShoppingCart :size="38" class="mx-auto text-neutral-300" aria-hidden="true" />
                            <p class="mt-3 font-semibold text-[#171717]">Your basket is empty</p>
                            <p class="mt-1 text-sm text-neutral-500">Add something useful to get started.</p>
                        </div>
                    </div>
                    <div class="mt-4 border border-neutral-200 bg-white p-5 text-sm text-[#40536b]">
                        <p class="font-bold text-[#171717]">Shopping with confidence</p>
                        <p class="mt-2 text-[#555555]">{{ storefront.deliveryMessage }} to {{ deliveryLocation }}.</p>
                        <button type="button" class="mt-3 text-sm font-semibold text-[#e21b23] hover:underline" @click="openCustomerService">Contact customer service</button>
                    </div>
                </aside>
            </div>

            <Modal :show="selectedProduct !== null" max-width="lg" @close="selectedProduct = null">
                <div v-if="selectedProduct" class="space-y-5 p-6">
                    <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold tracking-widest text-[#b9151b] uppercase">Product details</p><h2 class="mt-1 text-2xl font-black text-[#171717]">{{ selectedProduct.name }}</h2></div><button type="button" class="text-sm font-semibold text-neutral-500" @click="selectedProduct = null">Close</button></div>
                    <div v-if="productImages(selectedProduct).length" class="space-y-3"><div class="aspect-[4/3] overflow-hidden rounded-3xl bg-neutral-50"><img :src="productImages(selectedProduct)[selectedAngle]" :alt="`${selectedProduct.name} product image ${selectedAngle + 1}`" class="h-full w-full object-contain p-3" /></div><div class="flex gap-2 overflow-x-auto"><button v-for="(image, index) in productImages(selectedProduct)" :key="image" type="button" class="size-16 shrink-0 overflow-hidden rounded-xl border-2 bg-neutral-50" :class="selectedAngle === index ? 'border-[#e21b23]' : 'border-transparent'" @click="selectedAngle = index"><img :src="image" :alt="`${selectedProduct.name} thumbnail ${index + 1}`" class="h-full w-full object-contain p-1" /></button></div></div>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <span class="text-2xl font-black">{{ formatPrice(selectedProduct.price_minor) }}</span>
                            <span v-if="selectedProduct.compare_at_price_minor && selectedProduct.compare_at_price_minor > selectedProduct.price_minor" class="ml-2 text-base text-neutral-400 line-through">{{ formatPrice(selectedProduct.compare_at_price_minor) }}</span>
                        </div>
                        <button type="button" class="rounded-full bg-[#e21b23] px-5 py-3 text-sm font-bold text-white" @click="addToCart(selectedProduct); selectedProduct = null">Add to cart</button>
                    </div>
                </div>
            </Modal>

            <Modal :show="locationModalOpen" max-width="md" @close="locationModalOpen = false">
                <div class="space-y-5 p-6">
                    <div>
                        <p class="text-xs font-bold tracking-widest text-[#b9151b] uppercase">Delivery location</p>
                        <h2 class="mt-1 text-2xl font-black text-[#171717]">Choose your delivery area</h2>
                        <p class="mt-2 text-sm text-neutral-500">Select where you want your order delivered.</p>
                    </div>
                    <div class="grid gap-2">
                        <button
                            v-for="location in storefront.deliveryLocations"
                            :key="location"
                            type="button"
                            class="flex items-center gap-3 rounded-xl border px-4 py-3 text-left text-sm font-semibold transition"
                            :class="deliveryLocation === location ? 'border-[#e21b23] bg-red-50 text-[#b9151b]' : 'border-neutral-200 hover:border-[#e21b23]'"
                            @click="selectLocation(location)"
                        >
                            <MapPin :size="18" aria-hidden="true" />
                            {{ location }}
                        </button>
                    </div>
                </div>
            </Modal>

            <Modal :show="customerServiceOpen" max-width="md" @close="customerServiceOpen = false">
                <div class="space-y-5 p-6">
                    <div>
                        <p class="text-xs font-bold tracking-widest text-[#b9151b] uppercase">Support</p>
                        <h2 class="mt-1 text-2xl font-black text-[#171717]">{{ storefront.customerService.title }}</h2>
                        <p class="mt-2 text-sm text-neutral-600">{{ storefront.customerService.message }}</p>
                    </div>
                    <div class="space-y-3 text-sm">
                        <a :href="`tel:${storefront.customerService.phone}`" class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3 font-semibold text-[#171717] hover:border-[#e21b23]">
                            <Phone :size="18" class="text-[#e21b23]" aria-hidden="true" />
                            {{ storefront.customerService.phone }}
                        </a>
                        <a :href="`mailto:${storefront.customerService.email}`" class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3 font-semibold text-[#171717] hover:border-[#e21b23]">
                            <Mail :size="18" class="text-[#e21b23]" aria-hidden="true" />
                            {{ storefront.customerService.email }}
                        </a>
                        <p class="rounded-xl bg-neutral-50 px-4 py-3 text-neutral-600">{{ storefront.customerService.hours }}</p>
                    </div>
                </div>
            </Modal>

            <Modal :show="accountModalOpen" max-width="sm" @close="accountModalOpen = false">
                <div class="space-y-4 p-6">
                    <div>
                        <p class="text-xs font-bold tracking-widest text-[#b9151b] uppercase">Your account</p>
                        <h2 class="mt-1 text-xl font-black text-[#171717]">Guest shopper</h2>
                        <p class="mt-2 text-sm text-neutral-500">Browse products, build your basket, and checkout when you are ready.</p>
                    </div>
                    <p class="text-sm text-neutral-600">Delivering to <strong>{{ deliveryLocation }}</strong></p>
                    <button type="button" class="min-h-10 w-full rounded-full bg-[#e21b23] px-4 text-sm font-bold text-white" @click="accountModalOpen = false; scrollToBasket()">View basket ({{ cart.length }})</button>
                </div>
            </Modal>
        </div>
    </main>
</template>
