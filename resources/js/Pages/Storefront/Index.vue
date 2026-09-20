<script setup lang="ts">
import EnBadge from '@/Components/EnBadge.vue';
import EnCard from '@/Components/EnCard.vue';
import EnEmptyState from '@/Components/EnEmptyState.vue';
import Modal from '@/Components/Modal.vue';
import ProductArtwork from '@/Components/ProductArtwork.vue';
import { Head } from '@inertiajs/vue3';
import {
    ChevronRight,
    MapPin,
    Minus,
    Plus,
    Search,
    ShoppingCart,
    UserRound,
} from '@lucide/vue';
import { computed, ref } from 'vue';

type Product = {
    id: number;
    name: string;
    sku: string;
    price_minor: number;
    image_path?: string | null;
    image_gallery?: string[] | null;
};

type CartItem = Product & { quantity: number };

const props = defineProps<{
    products: Product[];
}>();

const search = ref('');
const cart = ref<CartItem[]>([]);
const tenant = String(route().params.tenant);
const selectedCategory = ref('All');
const selectedProduct = ref<Product | null>(null);
const selectedAngle = ref(0);

const categories = ['All', 'Pantry', 'Drinks', 'Household'];

const categoryKeywords: Record<string, string[]> = {
    Pantry: ['oil', 'rice', 'milk', 'flour', 'sugar', 'porridge'],
    Drinks: ['water', 'juice', 'drink', 'soda'],
    Household: ['soap', 'tissue', 'wash', 'clean'],
};

const filteredProducts = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.products.filter(
        (product) => {
            const name = product.name.toLowerCase();
            const matchesSearch = term === '' || name.includes(term);
            const keywords = categoryKeywords[selectedCategory.value] ?? [];
            const matchesCategory =
                selectedCategory.value === 'All' ||
                keywords.some((keyword) => name.includes(keyword));

            return matchesSearch && matchesCategory;
        },
    );
});

const totalMinor = computed(() =>
    cart.value.reduce(
        (total, item) => total + item.price_minor * item.quantity,
        0,
    ),
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
    <Head title="Shop" />
    <main class="min-h-screen bg-[#f4f4f2] text-[#171717]">
        <header class="bg-[#171717] text-white">
            <div class="mx-auto flex max-w-[1500px] items-center gap-5 px-4 py-3 sm:px-8">
                <a :href="route('tenant.home', { tenant })" class="shrink-0 text-xl font-black tracking-tight">
                    enabl<span class="text-[#e21b23]">store</span>
                </a>
                <div class="hidden items-center gap-2 text-xs leading-tight md:flex">
                    <MapPin :size="19" class="text-white" aria-hidden="true" />
                    <span class="text-neutral-300">Deliver to</span>
                    <strong class="block">Your location</strong>
                </div>
                <label class="flex min-w-0 flex-1 overflow-hidden rounded-md bg-white focus-within:ring-2 focus-within:ring-[#e21b23]">
                    <span class="sr-only">Search products</span>
                    <input v-model="search" type="search" placeholder="Search Enablstore" aria-label="Search products" class="min-h-11 min-w-0 flex-1 border-0 bg-transparent px-4 text-sm text-[#172337] outline-none placeholder:text-neutral-500" />
                    <button type="button" class="grid w-12 shrink-0 place-items-center bg-[#e21b23] text-white hover:bg-[#b9151b]" aria-label="Search">
                        <Search :size="21" aria-hidden="true" />
                    </button>
                </label>
                <div class="hidden items-center gap-2 text-xs sm:flex">
                    <UserRound :size="20" aria-hidden="true" />
                    <span>Hello, shopper<br /><strong class="text-sm">Account &amp; Lists</strong></span>
                </div>
                <div class="flex items-end gap-1 text-xs font-bold">
                    <ShoppingCart :size="28" aria-hidden="true" />
                    <span>{{ cart.length }}</span>
                </div>
            </div>
            <nav class="bg-[#2b2b2b]">
                <div class="mx-auto flex max-w-[1500px] items-center gap-6 overflow-x-auto px-4 py-2 text-sm font-semibold whitespace-nowrap sm:px-8">
                    <span class="text-[#ff9696]">Today's Deals</span>
                    <span>Best Sellers</span>
                    <span>New Arrivals</span>
                    <span>Customer Service</span>
                    <span class="ml-auto hidden text-neutral-300 lg:block">Fast local delivery on every order</span>
                </div>
            </nav>
        </header>

        <div class="mx-auto max-w-[1500px] px-4 py-5 sm:px-8">
            <section class="rounded-sm bg-[#fbe5e5] px-5 py-7 sm:px-10 sm:py-9">
                <div class="max-w-2xl">
                    <EnBadge tone="success">In stock and ready to ship</EnBadge>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-[#171717] sm:text-4xl">Everyday essentials, delivered simply.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-[#555555]">Shop trusted products for your home, pantry, and daily routine. Add what you need and we will take care of the rest.</p>
                </div>
            </section>

            <div class="mt-5 flex items-center gap-3 overflow-x-auto border-b border-neutral-300 pb-3">
                <button v-for="category in categories" :key="category" type="button" class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition" :class="selectedCategory === category ? 'border-[#e21b23] bg-[#e21b23] text-white' : 'border-neutral-300 bg-white text-[#555555] hover:border-[#e21b23]'" @click="selectedCategory = category">
                    {{ category }}
                </button>
            </div>

            <div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <section>
                    <div class="mb-4 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold tracking-widest text-[#b9151b] uppercase">Featured selection</p>
                            <h2 class="mt-1 text-2xl font-black text-[#171717]">Shop popular products</h2>
                        </div>
                        <span class="text-sm text-[#40536b]">{{ filteredProducts.length }} items</span>
                    </div>
                    <div v-if="filteredProducts.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <article v-for="product in filteredProducts" :key="product.id" class="group flex cursor-pointer flex-col rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-[#e21b23] hover:shadow-lg" @click="openProduct(product)">
                            <ProductArtwork :name="product.name" :sku="product.sku" :image-path="product.image_path" size="large" />
                            <div class="flex flex-1 flex-col pt-4">
                                <p class="text-xs text-neutral-500">Enablstore choice</p>
                                <h3 class="mt-1 line-clamp-2 min-h-12 text-base font-semibold text-[#171717]">{{ product.name }}</h3>
                                <div class="mt-3 flex items-baseline gap-2">
                                    <span class="text-xl font-black text-[#171717]">{{ formatPrice(product.price_minor) }}</span>
                                    <span class="text-xs text-neutral-500">in stock</span>
                                </div>
                                <button type="button" class="mt-4 min-h-10 rounded-full bg-[#e21b23] px-4 text-sm font-bold text-white transition hover:bg-[#b9151b]" @click.stop="addToCart(product)">
                                    Add to cart
                                </button>
                            </div>
                        </article>
                    </div>
                    <EnCard v-else>
                        <EnEmptyState title="No products found" description="Try another search or browse all products." />
                    </EnCard>
                </section>

                <aside class="lg:sticky lg:top-5 lg:self-start">
                    <div class="border border-neutral-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-black text-[#171717]">Your basket</h2>
                            <ShoppingCart :size="22" class="text-[#e21b23]" aria-hidden="true" />
                        </div>
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
                        <p class="mt-2 text-[#555555]">Quality products, clear pricing, and reliable local fulfilment.</p>
                    </div>
                </aside>
            </div>

            <Modal :show="selectedProduct !== null" max-width="lg" @close="selectedProduct = null">
                <div v-if="selectedProduct" class="space-y-5 p-6">
                    <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold tracking-widest text-[#b9151b] uppercase">Product details</p><h2 class="mt-1 text-2xl font-black text-[#171717]">{{ selectedProduct.name }}</h2></div><button type="button" class="text-sm font-semibold text-neutral-500" @click="selectedProduct = null">Close</button></div>
                    <div v-if="productImages(selectedProduct).length" class="space-y-3"><div class="aspect-[4/3] overflow-hidden rounded-3xl bg-neutral-50"><img :src="productImages(selectedProduct)[selectedAngle]" :alt="`${selectedProduct.name} product image ${selectedAngle + 1}`" class="h-full w-full object-contain p-3" /></div><div class="flex gap-2 overflow-x-auto"><button v-for="(image, index) in productImages(selectedProduct)" :key="image" type="button" class="size-16 shrink-0 overflow-hidden rounded-xl border-2 bg-neutral-50" :class="selectedAngle === index ? 'border-[#e21b23]' : 'border-transparent'" @click="selectedAngle = index"><img :src="image" :alt="`${selectedProduct.name} thumbnail ${index + 1}`" class="h-full w-full object-contain p-1" /></button></div></div>
                    <div class="flex items-center justify-between"><span class="text-2xl font-black">{{ formatPrice(selectedProduct.price_minor) }}</span><button type="button" class="rounded-full bg-[#e21b23] px-5 py-3 text-sm font-bold text-white" @click="addToCart(selectedProduct); selectedProduct = null">Add to cart</button></div>
                </div>
            </Modal>
        </div>
    </main>
</template>
