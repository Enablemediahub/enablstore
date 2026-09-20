<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import BarcodeScanner from '@/Components/BarcodeScanner.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import EnEmptyState from '@/Components/EnEmptyState.vue';
import Modal from '@/Components/Modal.vue';
import ProductArtwork from '@/Components/ProductArtwork.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { Edit3, LayoutGrid, List, Plus, Rows3, ScanLine, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

type Product = {
    id: number;
    name: string;
    sku: string;
    barcode?: string | null;
    category_id?: number | null;
    image_path?: string | null;
    price_minor: number;
    purchase_unit: string;
    units_per_purchase: number;
    available_in_pos: boolean;
    available_online: boolean;
    inventory_stock?: { quantity: number; low_stock_threshold: number };
};

type Category = { id: number; name: string };

const props = defineProps<{ products: { data: Product[]; current_page: number; last_page: number; links: Array<{ url: string | null; label: string; active: boolean }> }; categories: Category[]; catalogueMode: 'shared' | 'separate_online' }>();
const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
const modalOpen = ref(false);
const scannerOpen = ref(false);
const barcodeLookingUp = ref(false);
const barcodeLookupMessage = ref('');
const editingProduct = ref<Product | null>(null);
const displayMode = ref<'grid' | 'thumbnail' | 'list'>('list');

const form = useForm({
    name: '',
    sku: '',
    barcode: '',
    image: null as File | null,
    images: [] as File[],
    category_id: null as number | null,
    available_in_pos: true,
    available_online: true,
    price_cedis: 0,
    cost_cedis: 0,
    purchase_unit: 'unit',
    units_per_purchase: 1,
    initial_quantity: 0,
    low_stock_threshold: 5,
});

const formatPrice = (minor: number): string =>
    new Intl.NumberFormat('en-GH', { style: 'currency', currency: 'GHS' }).format(minor / 100);

const resetForm = (): void => {
    form.reset();
    form.clearErrors();
};

const openCreateModal = (): void => {
    editingProduct.value = null;
    resetForm();
    modalOpen.value = true;
};

const openEditModal = (product: Product): void => {
    editingProduct.value = product;
    form.name = product.name;
    form.sku = product.sku;
    form.barcode = product.barcode ?? '';
    form.image = null;
    form.category_id = product.category_id ?? null;
    form.available_in_pos = product.available_in_pos;
    form.available_online = product.available_online;
    form.price_cedis = product.price_minor / 100;
    form.cost_cedis = 0;
    form.purchase_unit = product.purchase_unit;
    form.units_per_purchase = product.units_per_purchase;
    form.initial_quantity = product.inventory_stock?.quantity ?? 0;
    form.low_stock_threshold = product.inventory_stock?.low_stock_threshold ?? 5;
    form.clearErrors();
    modalOpen.value = true;
};

const closeModal = (): void => {
    if (!form.processing) {
        modalOpen.value = false;
        scannerOpen.value = false;
    }
};

const handleBarcode = async (code: string): Promise<void> => {
    form.barcode = code;
    if (form.sku === '') form.sku = code;
    scannerOpen.value = false;
    barcodeLookingUp.value = true;
    barcodeLookupMessage.value = '';

    try {
        const response = await axios.get(route('tenant.products.barcode-lookup', { tenant, barcode: code }));
        const product = response.data.product;

        if (response.data.found && product?.name) {
            form.name = product.name;
            const categoryName = String(product.category ?? '').split('>').pop()?.trim().toLowerCase();
            const matchingCategory = props.categories.find((category) => category.name.toLowerCase() === categoryName);
            if (matchingCategory) {
                form.category_id = matchingCategory.id;
            }
            barcodeLookupMessage.value = response.data.message;
        } else {
            barcodeLookupMessage.value = response.data.message;
        }
    } catch {
        barcodeLookupMessage.value = 'Barcode lookup failed. Enter the product details manually.';
    } finally {
        barcodeLookingUp.value = false;
    }
};

const handleImageChange = (event: Event): void => {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);
    form.image = files[0] ?? null;
    form.images = files.slice(1);
};

const deleteProduct = (product: Product): void => {
    if (!window.confirm(`Remove ${product.name} from the active catalogue?`)) {
        return;
    }

    router.delete(route('tenant.products.destroy', { tenant, product: product.id }));
};

const submit = (): void => {
    form.transform((data) => ({
        name: data.name,
        sku: data.sku.trim(),
        barcode: data.barcode || null,
        image: data.image,
        images: data.images,
        category_id: data.category_id,
        available_in_pos: data.available_in_pos,
        available_online: data.available_online,
        price_minor: Math.round(Number(data.price_cedis) * 100),
        cost_minor: Math.round(Number(data.cost_cedis) * 100),
        purchase_unit: data.purchase_unit,
        units_per_purchase: data.units_per_purchase,
        initial_quantity: data.initial_quantity,
        low_stock_threshold: data.low_stock_threshold,
        _method: editingProduct.value ? 'patch' : undefined,
    }));

    const options = {
        forceFormData: true,
        onSuccess: () => {
            modalOpen.value = false;
            editingProduct.value = null;
            resetForm();
        },
    };

    if (editingProduct.value) {
        form.post(route('tenant.products.update', { tenant, product: editingProduct.value.id }), options);
        return;
    }

    form.post(route('tenant.products.store', { tenant }), options);
};
</script>

<template>
    <Head title="Products" />
    <main class="min-h-screen bg-neutral-50">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel :tenant="tenant" current="products" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" />
            <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <button type="button" class="mb-5 inline-flex items-center gap-2 rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm font-medium text-neutral-700 shadow-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button>
                <div class="mx-auto max-w-7xl space-y-8">
                    <header class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-red-700">Store catalogue</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-neutral-900">Products</h1>
                            <p class="mt-2 text-sm text-neutral-500">Manage prices, stock, barcodes, and optional product images.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-1 rounded-md border border-neutral-200 bg-white p-1">
                                <button v-for="mode in [{ value: 'grid', label: 'Grid', icon: LayoutGrid }, { value: 'thumbnail', label: 'Thumbnail', icon: Rows3 }, { value: 'list', label: 'List', icon: List }]" :key="mode.value" type="button" class="rounded p-2" :class="displayMode === mode.value ? 'bg-red-50 text-red-700' : 'text-neutral-500 hover:bg-neutral-50'" :aria-label="`${mode.label} view`" @click="displayMode = mode.value as 'grid' | 'thumbnail' | 'list'"><component :is="mode.icon" :size="16" aria-hidden="true" /></button>
                            </div>
                            <EnButton @click="openCreateModal"><Plus :size="18" aria-hidden="true" /> Add product</EnButton>
                        </div>
                    </header>

                    <EnCard>
                        <div v-if="displayMode === 'list' && props.products.data.length" class="overflow-x-auto">
                            <table class="w-full min-w-170 text-left text-sm">
                                <thead class="border-b border-neutral-100 text-xs text-neutral-500 uppercase">
                                    <tr><th class="px-3 py-3 font-semibold">Product</th><th class="px-3 py-3 font-semibold">SKU / Barcode</th><th class="px-3 py-3 font-semibold">Price</th><th class="px-3 py-3 font-semibold">Stock</th><th class="px-3 py-3 text-right font-semibold">Action</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product in props.products.data" :key="product.id" class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50">
                                        <td class="px-3 py-4"><div class="flex items-center gap-3"><ProductArtwork :name="product.name" :sku="product.sku" :image-path="product.image_path" /><span class="font-semibold text-neutral-900">{{ product.name }}</span></div></td>
                                        <td class="px-3 py-4"><p class="font-mono text-xs text-neutral-700">{{ product.sku }}</p><p class="mt-1 text-xs text-neutral-500">{{ product.barcode || 'No barcode' }}</p></td>
                                        <td class="px-3 py-4 font-mono text-neutral-700">{{ formatPrice(product.price_minor) }}</td>
                                        <td class="px-3 py-4"><span :class="(product.inventory_stock?.quantity ?? 0) <= (product.inventory_stock?.low_stock_threshold ?? 0) ? 'font-semibold text-red-700' : 'text-neutral-700'">{{ product.inventory_stock?.quantity ?? 0 }}</span></td>
                                        <td class="px-3 py-4 text-right"><div class="flex justify-end gap-2"><button type="button" class="inline-flex items-center gap-2 rounded-md border border-neutral-200 px-3 py-2 text-sm font-semibold text-neutral-700 hover:border-red-300 hover:text-red-700" @click="openEditModal(product)"><Edit3 :size="16" aria-hidden="true" /> Edit</button><button type="button" class="inline-flex items-center gap-2 rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50" @click="deleteProduct(product)"><Trash2 :size="16" aria-hidden="true" /> Delete</button></div></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else-if="displayMode !== 'list' && props.products.data.length" class="grid gap-4" :class="displayMode === 'grid' ? 'sm:grid-cols-2 xl:grid-cols-3' : 'grid-cols-2 sm:grid-cols-4 xl:grid-cols-5'">
                            <article v-for="product in props.products.data" :key="product.id" class="rounded-lg border border-neutral-100 bg-white p-3 shadow-sm">
                                <ProductArtwork :name="product.name" :sku="product.sku" :image-path="product.image_path" :size="displayMode === 'grid' ? 'large' : 'compact'" />
                                <div class="mt-3"><h2 class="font-semibold text-neutral-900">{{ product.name }}</h2><p class="mt-1 font-mono text-xs text-neutral-500">{{ product.sku }}</p><p class="mt-2 font-mono text-sm text-neutral-700">{{ formatPrice(product.price_minor) }}</p><p class="mt-1 text-xs text-neutral-500">{{ product.inventory_stock?.quantity ?? 0 }} in stock</p></div>
                                <div class="mt-3 flex gap-2"><button type="button" class="flex-1 rounded-md border border-neutral-200 px-2 py-1.5 text-xs font-semibold text-neutral-700" @click="openEditModal(product)"><Edit3 :size="14" class="mx-auto" aria-hidden="true" /></button><button type="button" class="rounded-md border border-red-200 px-2 py-1.5 text-red-700" @click="deleteProduct(product)"><Trash2 :size="14" aria-hidden="true" /></button></div>
                            </article>
                        </div>
                        <EnEmptyState v-else title="No products yet" description="Create your first product to start managing stock and sales." />
                        <div v-if="props.products.last_page > 1" class="mt-5 flex flex-wrap items-center justify-center gap-2 border-t border-neutral-100 pt-5">
                            <button v-for="link in props.products.links" :key="link.label" type="button" class="rounded-md border px-3 py-2 text-sm" :class="link.active ? 'border-red-600 bg-red-600 text-white' : 'border-neutral-200 text-neutral-700 hover:bg-neutral-50'" :disabled="!link.url" @click="link.url && router.visit(link.url)"><span v-html="link.label" /></button>
                        </div>
                    </EnCard>
                </div>
            </section>
        </div>
    </main>

    <Modal :show="modalOpen" max-width="lg" @close="closeModal">
        <form class="space-y-6 p-6" enctype="multipart/form-data" @submit.prevent="submit">
            <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold tracking-[0.18em] text-red-700 uppercase">Product catalogue</p><h2 class="mt-1 text-2xl font-bold text-neutral-900">{{ editingProduct ? 'Edit product' : 'Add product' }}</h2><p class="mt-1 text-sm text-neutral-500">Images are optional and can be added or replaced later.</p></div><button type="button" class="text-sm font-semibold text-neutral-500 hover:text-neutral-900" @click="closeModal">Close</button></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Product name<input v-model="form.name" type="text" required class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span v-if="form.errors.name" class="text-danger block text-sm">{{ form.errors.name }}</span></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">SKU<input v-model="form.sku" type="text" placeholder="Leave blank to auto-generate" class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span v-if="form.errors.sku" class="text-danger block text-sm">{{ form.errors.sku }}</span></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Barcode<span class="flex gap-2"><input v-model="form.barcode" type="text" class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-md border border-neutral-300 px-3 text-sm font-semibold text-neutral-700 hover:border-red-300 hover:text-red-700" @click="scannerOpen = true"><ScanLine :size="17" aria-hidden="true" /> Scan</button></span><span v-if="barcodeLookingUp" class="block text-xs text-neutral-500">Looking up barcode…</span><span v-else-if="barcodeLookupMessage" class="block text-xs text-neutral-500">{{ barcodeLookupMessage }}</span><span v-if="form.errors.barcode" class="text-danger block text-sm">{{ form.errors.barcode }}</span></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Category<select v-model="form.category_id" class="min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option :value="null">Uncategorised</option><option v-for="category in props.categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Product images (optional)<input type="file" multiple accept="image/jpeg,image/png,image/webp" class="block min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-red-50 file:px-3 file:py-1.5 file:font-semibold file:text-red-700" @change="handleImageChange" /><span v-if="form.image" class="block text-xs text-neutral-500">{{ [form.image, ...form.images].length }} image(s) selected</span><span v-if="form.errors.image" class="text-danger block text-sm">{{ form.errors.image }}</span></label>
                <fieldset class="sm:col-span-2"><legend class="text-sm font-medium text-neutral-700">Sales channels</legend><div class="mt-2 flex flex-wrap gap-4 text-sm text-neutral-700"><label class="flex items-center gap-2"><input v-model="form.available_in_pos" type="checkbox" /> Point of Sale</label><label class="flex items-center gap-2"><input v-model="form.available_online" type="checkbox" /> Online Store</label></div></fieldset>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Selling price (cedis)<input v-model.number="form.price_cedis" type="number" min="0" step="0.01" required class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Cost price (cedis)<input v-model.number="form.cost_cedis" type="number" min="0" step="0.01" class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Purchase unit<input v-model="form.purchase_unit" type="text" placeholder="box" class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span class="block text-xs text-neutral-500">Use box, carton, case, or unit.</span></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Units inside purchase unit<input v-model.number="form.units_per_purchase" type="number" min="1" step="1" required class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span class="block text-xs text-neutral-500">Example: 12 bottles per box.</span></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Stock quantity<input v-model.number="form.initial_quantity" type="number" min="0" step="1" required class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
                <label class="space-y-1.5 text-sm font-medium text-neutral-700">Low stock alert at<input v-model.number="form.low_stock_threshold" type="number" min="0" step="1" required class="min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
            </div>
            <div class="flex justify-end gap-3 border-t border-neutral-100 pt-5"><button type="button" class="rounded-md border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:bg-neutral-50" @click="closeModal">Cancel</button><EnButton type="submit" :loading="form.processing">{{ editingProduct ? 'Save changes' : 'Add product' }}</EnButton></div>
        </form>
    </Modal>
    <BarcodeScanner :open="scannerOpen" @close="scannerOpen = false" @detected="handleBarcode" />
</template>