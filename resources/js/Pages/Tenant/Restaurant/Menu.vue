<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnInput from '@/Components/EnInput.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ImagePlus, LayoutGrid, List, Pencil, Plus, Rows3, Tags, Trash2, Utensils } from '@lucide/vue';
import { computed, onUnmounted, ref } from 'vue';

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

const props = defineProps<{
    tenant: string;
    restaurantName: string;
    menuItems: MenuItem[];
    categories: string[];
    status?: string | null;
}>();

const mobilePanelOpen = ref(false);
const displayMode = ref<'grid' | 'thumbnail' | 'list'>('thumbnail');
const modalOpen = ref(false);
const editingItem = ref<MenuItem | null>(null);
const imagePreview = ref<string | null>(null);
const imageInput = ref<HTMLInputElement | null>(null);
let objectUrl: string | null = null;
const form = useForm({
    name: '',
    category: '',
    description: '',
    price_ghs: '' as number | string,
    unit_label: 'plate',
    image: null as File | null,
});
const availableCategories = computed(() => [...new Set([
    ...props.categories,
    ...props.menuItems.map((item) => item.category),
])]);

const formatPrice = (minor: number): string => new Intl.NumberFormat('en-GH', {
    style: 'currency',
    currency: 'GHS',
}).format(minor / 100);

const clearPreview = (): void => {
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = null;
    imagePreview.value = null;
};

const resetForm = (): void => {
    form.reset();
    form.clearErrors();
    editingItem.value = null;
    clearPreview();
    if (imageInput.value) imageInput.value.value = '';
};

const openCreateModal = (): void => {
    resetForm();
    modalOpen.value = true;
};

const closeModal = (): void => {
    if (form.processing) return;

    modalOpen.value = false;
    resetForm();
};

const selectImage = (event: Event): void => {
    const file = (event.currentTarget as HTMLInputElement).files?.[0] ?? null;
    form.image = file;
    clearPreview();
    if (file) {
        objectUrl = URL.createObjectURL(file);
        imagePreview.value = objectUrl;
    } else {
        imagePreview.value = editingItem.value?.image_url ?? null;
    }
};

const editItem = (item: MenuItem): void => {
    resetForm();
    editingItem.value = item;
    form.name = item.name;
    form.category = item.category;
    form.description = item.description ?? '';
    form.price_ghs = item.price_minor / 100;
    form.unit_label = item.unit_label;
    imagePreview.value = item.image_url;
    modalOpen.value = true;
};

const submit = (): void => {
    form.transform((data) => editingItem.value ? { ...data, _method: 'patch' } : data);
    form.post(
        editingItem.value
            ? route('tenant.foodstore.menu.update', { tenant: props.tenant, menuItem: editingItem.value.id })
            : route('tenant.foodstore.menu.store', { tenant: props.tenant }),
        {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                modalOpen.value = false;
                resetForm();
            },
        },
    );
};

const toggleAvailability = (item: MenuItem): void => router.patch(
    route('tenant.foodstore.menu.update', { tenant: props.tenant, menuItem: item.id }),
    { is_available: !item.is_available },
    { preserveScroll: true },
);

const removeItem = (item: MenuItem): void => {
    if (!window.confirm(`Remove ${item.name} from the food menu? Existing sales will be kept.`)) return;

    router.delete(route('tenant.foodstore.menu.destroy', { tenant: props.tenant, menuItem: item.id }), {
        preserveScroll: true,
        onSuccess: () => {
            if (editingItem.value?.id === item.id) resetForm();
        },
    });
};

onUnmounted(clearPreview);
</script>

<template>
    <Head :title="`Food menu - ${restaurantName}`" />
    <main class="min-h-screen bg-neutral-50 text-neutral-900">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel :tenant="tenant" current="food-menu" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" />
            <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <button type="button" class="mb-5 inline-flex min-h-10 items-center gap-2 rounded-md border border-neutral-200 bg-white px-3 text-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button>
                <div class="mx-auto max-w-7xl space-y-6">
                    <header class="flex flex-wrap items-end justify-between gap-4">
                        <div><p class="text-sm font-semibold text-emerald-800">FoodStore setup</p><h1 class="mt-1 text-3xl font-black">Food menu</h1><p class="mt-2 text-sm text-neutral-500">Add dishes and packages with a photo and a price per serving.</p></div>
                        <div class="flex flex-wrap items-center gap-2"><span class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-900"><Utensils :size="17" aria-hidden="true" /> {{ restaurantName }}</span><Link :href="route('tenant.categories.index', { tenant })" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-neutral-300 bg-white px-3 text-sm font-semibold text-neutral-700 hover:border-emerald-300 hover:text-emerald-800"><Tags :size="16" aria-hidden="true" /> Categories</Link></div>
                    </header>
                    <p v-if="status" class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ status }}</p>

                    <div>
                        <section>
                            <div class="mb-4 flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-xl font-bold">Menu items</h2><p class="mt-1 text-sm text-neutral-500">{{ menuItems.length }} items · {{ menuItems.filter((item) => item.is_available).length }} available</p></div><div class="flex flex-wrap items-center gap-2"><div class="flex items-center gap-1 rounded-md border border-neutral-200 bg-white p-1" aria-label="Menu view"><button v-for="mode in [{ value: 'grid', label: 'Grid', icon: LayoutGrid }, { value: 'thumbnail', label: 'Thumbnail', icon: Rows3 }, { value: 'list', label: 'List', icon: List }]" :key="mode.value" type="button" class="rounded p-2" :class="displayMode === mode.value ? 'bg-emerald-50 text-emerald-800' : 'text-neutral-500 hover:bg-neutral-50'" :aria-label="`${mode.label} view`" :aria-pressed="displayMode === mode.value" @click="displayMode = mode.value as 'grid' | 'thumbnail' | 'list'"><component :is="mode.icon" :size="16" aria-hidden="true" /></button></div><button type="button" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-emerald-700 px-3 text-sm font-bold text-white hover:bg-emerald-800" @click="openCreateModal"><Plus :size="16" aria-hidden="true" /> New food item</button></div></div>
                            <div v-if="menuItems.length" class="gap-4" :class="displayMode === 'grid' ? 'grid sm:grid-cols-2 2xl:grid-cols-3' : displayMode === 'thumbnail' ? 'grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-5' : 'flex flex-col gap-2'">
                                <article v-for="item in menuItems" :key="item.id" class="overflow-hidden rounded-md border border-neutral-200 bg-white" :class="displayMode === 'list' ? 'flex items-center gap-3 p-3' : ''">
                                    <div class="bg-emerald-50" :class="displayMode === 'list' ? 'size-16 shrink-0 rounded-md' : displayMode === 'thumbnail' ? 'aspect-square' : 'aspect-[4/3]'">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                                        <div v-else class="grid h-full place-items-center text-emerald-700"><Utensils :size="38" aria-hidden="true" /></div>
                                    </div>
                                    <div class="min-w-0 flex-1" :class="displayMode === 'list' ? '' : 'p-4'"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="text-xs font-bold uppercase text-emerald-800">{{ item.category }}</p><h3 class="mt-1 truncate font-bold">{{ item.name }}</h3></div><span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold" :class="item.is_available ? 'bg-emerald-50 text-emerald-800' : 'bg-neutral-100 text-neutral-600'">{{ item.is_available ? 'Available' : 'Paused' }}</span></div>
                                        <p v-if="item.description" class="mt-2 line-clamp-2 text-sm text-neutral-500">{{ item.description }}</p>
                                        <p class="mt-3 font-mono text-sm font-semibold">{{ formatPrice(item.price_minor) }} <span class="font-sans font-normal text-neutral-500">/ {{ item.unit_label }}</span></p>
                                        <div class="mt-4 flex flex-wrap gap-2 border-t border-neutral-100 pt-3"><button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-neutral-200 px-2.5 text-xs font-semibold text-neutral-700 hover:border-emerald-300 hover:text-emerald-800" @click="editItem(item)"><Pencil :size="14" aria-hidden="true" /> Edit</button><button type="button" class="min-h-9 rounded-md border border-neutral-200 px-2.5 text-xs font-semibold text-neutral-700 hover:border-emerald-300 hover:text-emerald-800" @click="toggleAvailability(item)">{{ item.is_available ? 'Pause' : 'Enable' }}</button><button type="button" class="ml-auto grid size-9 place-items-center rounded-md text-neutral-500 hover:bg-red-50 hover:text-red-700" :aria-label="`Remove ${item.name}`" @click="removeItem(item)"><Trash2 :size="15" aria-hidden="true" /></button></div>
                                    </div>
                                </article>
                            </div>
                            <p v-else class="rounded-md border border-dashed border-neutral-300 bg-white px-5 py-12 text-center text-sm text-neutral-500">No food items yet.</p>
                        </section>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <Modal :show="modalOpen" max-width="xl" @close="closeModal">
        <form class="space-y-5 p-6" @submit.prevent="submit">
            <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-wide text-emerald-800">{{ editingItem ? 'Edit food item' : 'New food item' }}</p><h2 class="mt-1 text-xl font-bold">{{ editingItem ? editingItem.name : 'Add to menu' }}</h2></div><button type="button" class="text-sm font-semibold text-neutral-500 hover:text-neutral-900" @click="closeModal">Close</button></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <EnInput id="food-name" v-model="form.name" label="Food name" required />
                <div>
                    <label for="food-category" class="block text-sm font-medium text-neutral-700">Category</label>
                    <div class="mt-1.5 flex gap-2">
                        <select id="food-category" v-model="form.category" required class="min-h-11 min-w-0 flex-1 rounded-md border border-neutral-300 bg-white px-3 text-sm">
                            <option disabled value="">Select a category</option>
                            <option v-for="category in availableCategories" :key="category" :value="category">{{ category }}</option>
                        </select>
                        <Link :href="route('tenant.categories.index', { tenant })" class="inline-flex min-h-11 shrink-0 items-center gap-1.5 rounded-md border border-neutral-300 px-2.5 text-xs font-semibold text-neutral-700 hover:border-emerald-300 hover:text-emerald-800"><Tags :size="14" aria-hidden="true" /> Manage</Link>
                    </div>
                    <p v-if="!availableCategories.length" class="mt-1 text-xs text-neutral-500">Create a category to add food items.</p>
                    <p v-if="form.errors.category" class="mt-1 text-xs text-red-700">{{ form.errors.category }}</p>
                </div>
                <EnInput id="food-unit" v-model="form.unit_label" label="Sold as" placeholder="plate, pack, bottle" required />
                <EnInput id="food-price" v-model="form.price_ghs" type="number" min="0.01" step="0.01" label="Price per unit (GHS)" required />
                <label class="block text-sm font-medium text-neutral-700 sm:col-span-2">Description (optional)<textarea v-model="form.description" maxlength="500" rows="2" class="mt-1.5 w-full rounded-md border border-neutral-300 px-3 py-2 text-sm" /></label>
                <div class="sm:col-span-2">
                    <label for="food-image" class="block text-sm font-medium text-neutral-700">Food photo</label>
                    <input id="food-image" ref="imageInput" type="file" accept="image/*" capture="environment" class="mt-1.5 block w-full text-sm text-neutral-600 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-700 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800" @change="selectImage" />
                    <p class="mt-1 text-xs text-neutral-500">Take a photo with your phone or choose an image. JPG, PNG, or WebP up to 5 MB.</p>
                    <div v-if="imagePreview" class="mt-3 aspect-[4/3] max-h-56 overflow-hidden rounded-md border border-neutral-200 bg-neutral-50 sm:max-w-sm"><img :src="imagePreview" :alt="form.name || 'Food photo preview'" class="h-full w-full object-cover" /></div>
                    <p v-else class="mt-3 flex min-h-24 items-center justify-center gap-2 rounded-md border border-dashed border-neutral-300 bg-neutral-50 text-sm text-neutral-500 sm:max-w-sm"><ImagePlus :size="18" aria-hidden="true" /> Photo preview</p>
                    <p v-if="form.errors.image" class="mt-1 text-xs text-red-700">{{ form.errors.image }}</p>
                </div>
                <p v-if="form.errors.name || form.errors.category || form.errors.unit_label || form.errors.price_ghs" class="text-sm text-red-700 sm:col-span-2">{{ form.errors.name || form.errors.category || form.errors.unit_label || form.errors.price_ghs }}</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-neutral-100 pt-4"><button type="button" class="min-h-10 rounded-md border border-neutral-300 px-4 text-sm font-semibold text-neutral-700 hover:bg-neutral-50" @click="closeModal">Cancel</button><button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-md bg-emerald-700 px-4 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-60" :disabled="form.processing">{{ form.processing ? 'Saving...' : editingItem ? 'Save changes' : 'Add food item' }}</button></div>
        </form>
    </Modal>
</template>
