<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnInput from '@/Components/EnInput.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ImagePlus, LayoutGrid, List, Pencil, Plus, Rows3, Tags, Trash2, Utensils } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';

type MenuItem = {
    id: number;
    name: string;
    category: string;
    description: string | null;
    price_minor: number;
    unit_label: string;
    image_url: string | null;
    is_available: boolean;
    option_groups: Array<{
        id: string;
        name: string;
        required: boolean;
        multiple: boolean;
        options: Array<{ id: string; name: string; price_minor: number }>;
    }>;
};

type EditableOptionGroup = {
    id: string;
    name: string;
    required: boolean;
    multiple: boolean;
    options: Array<{ id: string; name: string; price_ghs: number | string }>;
};

type OptionTemplate = {
    id: string;
    name: string;
    groups: EditableOptionGroup[];
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
const optionTemplateInput = ref<HTMLInputElement | null>(null);
let objectUrl: string | null = null;
const optionTemplates = ref<OptionTemplate[]>([]);
const selectedTemplateId = ref('');
const optionTemplateName = ref('');
const optionTemplateStorageKey = `enablstore-food-option-templates-${props.tenant}`;
const form = useForm({
    name: '',
    category: '',
    description: '',
    price_ghs: '' as number | string,
    unit_label: 'plate',
    option_groups: [] as EditableOptionGroup[],
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
    form.option_groups = item.option_groups.map((group) => ({
        ...group,
        options: group.options.map((option) => ({
            ...option,
            price_ghs: option.price_minor / 100,
        })),
    }));
    imagePreview.value = item.image_url;
    modalOpen.value = true;
};

const addOptionGroup = (): void => {
    form.option_groups.push({
        id: crypto.randomUUID(),
        name: '',
        required: false,
        multiple: false,
        options: [{ id: crypto.randomUUID(), name: '', price_ghs: 0 }],
    });
};

const addOption = (group: EditableOptionGroup): void => {
    group.options.push({ id: crypto.randomUUID(), name: '', price_ghs: 0 });
};

const parseOptionTemplate = (value: unknown): OptionTemplate | null => {
    if (!value || typeof value !== 'object') return null;
    const candidate = value as Partial<OptionTemplate>;
    if (typeof candidate.name !== 'string' || !candidate.name.trim() || !Array.isArray(candidate.groups) || !candidate.groups.length || candidate.groups.length > 8) return null;

    const groups: EditableOptionGroup[] = [];
    for (const group of candidate.groups) {
        if (!group || typeof group.name !== 'string' || !group.name.trim() || !Array.isArray(group.options) || !group.options.length || group.options.length > 15) return null;
        const options = group.options.map((option) => ({
            id: typeof option.id === 'string' ? option.id : crypto.randomUUID(),
            name: typeof option.name === 'string' ? option.name.trim() : '',
            price_ghs: option.price_ghs,
        }));
        if (options.some((option) => !option.name || !Number.isFinite(Number(option.price_ghs)) || Number(option.price_ghs) < 0)) return null;
        groups.push({
            id: typeof group.id === 'string' ? group.id : crypto.randomUUID(),
            name: group.name.trim(),
            required: Boolean(group.required),
            multiple: Boolean(group.multiple),
            options,
        });
    }

    return {
        id: typeof candidate.id === 'string' ? candidate.id : crypto.randomUUID(),
        name: candidate.name.trim(),
        groups,
    };
};

const persistOptionTemplates = (): void => {
    localStorage.setItem(optionTemplateStorageKey, JSON.stringify(optionTemplates.value));
};

const saveOptionTemplate = (): void => {
    const name = optionTemplateName.value.trim();
    const template = parseOptionTemplate({ name, groups: form.option_groups });
    if (!template) return;

    const existingIndex = optionTemplates.value.findIndex((entry) => entry.name.toLowerCase() === name.toLowerCase());
    if (existingIndex >= 0) {
        template.id = optionTemplates.value[existingIndex].id;
        optionTemplates.value[existingIndex] = template;
    } else {
        optionTemplates.value.push(template);
    }
    selectedTemplateId.value = template.id;
    optionTemplateName.value = '';
    persistOptionTemplates();
};

const applyOptionTemplate = (): void => {
    const template = optionTemplates.value.find((entry) => entry.id === selectedTemplateId.value);
    if (!template) return;
    form.option_groups = template.groups.map((group) => ({
        ...group,
        id: crypto.randomUUID(),
        options: group.options.map((option) => ({ ...option, id: crypto.randomUUID() })),
    }));
};

const exportOptionTemplates = (): void => {
    if (!optionTemplates.value.length) return;
    const file = new Blob([JSON.stringify(optionTemplates.value, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(file);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'foodstore-option-templates.json';
    link.click();
    URL.revokeObjectURL(url);
};

const importOptionTemplates = async (event: Event): Promise<void> => {
    const input = event.currentTarget as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    try {
        const parsed: unknown = JSON.parse(await file.text());
        const entries = Array.isArray(parsed) ? parsed : [parsed];
        const imported = entries.map(parseOptionTemplate).filter((template): template is OptionTemplate => template !== null);
        if (!imported.length) throw new Error('No valid option templates were found in that file.');

        for (const template of imported) {
            const existingIndex = optionTemplates.value.findIndex((entry) => entry.name.toLowerCase() === template.name.toLowerCase());
            if (existingIndex >= 0) optionTemplates.value[existingIndex] = template;
            else optionTemplates.value.push(template);
        }
        selectedTemplateId.value = imported[0].id;
        persistOptionTemplates();
    } catch (error) {
        window.alert(error instanceof Error ? error.message : 'Unable to import option templates.');
    } finally {
        input.value = '';
    }
};

onMounted(() => {
    try {
        const stored: unknown = JSON.parse(localStorage.getItem(optionTemplateStorageKey) ?? '[]');
        if (Array.isArray(stored)) optionTemplates.value = stored.map(parseOptionTemplate).filter((template): template is OptionTemplate => template !== null);
    } catch {
        optionTemplates.value = [];
    }
});

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
                                        <p v-if="item.option_groups.length" class="mt-1 text-xs text-neutral-500">{{ item.option_groups.length }} option group{{ item.option_groups.length === 1 ? '' : 's' }}</p>
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
                <section class="space-y-4 border-t border-neutral-100 pt-4 sm:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div><h3 class="font-semibold text-neutral-900">Choices and extras</h3><p class="mt-1 text-xs text-neutral-500">Optional groups add no choices by default. Leave this empty for a fixed-price item.</p></div>
                        <button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-emerald-200 px-3 text-xs font-semibold text-emerald-800 hover:bg-emerald-50" @click="addOptionGroup"><Plus :size="14" aria-hidden="true" /> Add choice group</button>
                    </div>
                    <div class="grid gap-2 rounded-md border border-neutral-200 bg-white p-3 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-end">
                        <label class="block text-xs font-semibold text-neutral-700">Reusable template
                            <select v-model="selectedTemplateId" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm font-normal" :disabled="!optionTemplates.length">
                                <option value="">{{ optionTemplates.length ? 'Choose a saved template' : 'No saved templates yet' }}</option>
                                <option v-for="template in optionTemplates" :key="template.id" :value="template.id">{{ template.name }}</option>
                            </select>
                        </label>
                        <button type="button" class="min-h-10 rounded-md border border-emerald-200 px-3 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 disabled:opacity-50" :disabled="!selectedTemplateId" @click="applyOptionTemplate">Use template</button>
                        <button type="button" class="min-h-10 rounded-md border border-neutral-300 px-3 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 disabled:opacity-50" :disabled="!optionTemplates.length" @click="exportOptionTemplates">Export templates</button>
                        <div class="flex flex-wrap gap-2 sm:col-span-3">
                            <input v-model="optionTemplateName" type="text" maxlength="60" placeholder="Name these choices, e.g. Rice plate proteins" class="min-h-10 min-w-48 flex-1 rounded-md border border-neutral-300 px-3 text-sm" />
                            <button type="button" class="min-h-10 rounded-md border border-emerald-200 px-3 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 disabled:opacity-50" :disabled="!form.option_groups.length || !optionTemplateName.trim()" @click="saveOptionTemplate">Save choices as template</button>
                            <button type="button" class="min-h-10 rounded-md border border-neutral-300 px-3 text-xs font-semibold text-neutral-700 hover:bg-neutral-50" @click="optionTemplateInput?.click()">Import templates</button>
                            <input ref="optionTemplateInput" type="file" accept="application/json,.json" class="hidden" @change="importOptionTemplates" />
                        </div>
                        <p class="text-xs text-neutral-500 sm:col-span-3">Templates are saved in this browser. Export a JSON file to use them on another device.</p>
                    </div>
                    <div v-for="(group, groupIndex) in form.option_groups" :key="group.id" class="space-y-3 rounded-md border border-neutral-200 bg-neutral-50 p-4">
                        <div class="grid gap-3 sm:grid-cols-[1fr_auto_auto_auto] sm:items-end">
                            <label class="block text-xs font-semibold text-neutral-700">Group name<input v-model="group.name" required maxlength="60" placeholder="Protein, Extras" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm font-normal" /></label>
                            <label class="flex min-h-10 items-center gap-2 text-xs font-medium text-neutral-700"><input v-model="group.required" type="checkbox" class="rounded border-neutral-300 text-emerald-700 focus:ring-emerald-600" /> Required</label>
                            <label class="flex min-h-10 items-center gap-2 text-xs font-medium text-neutral-700"><input v-model="group.multiple" type="checkbox" class="rounded border-neutral-300 text-emerald-700 focus:ring-emerald-600" /> Allow multiple</label>
                            <button type="button" class="min-h-9 rounded-md px-2 text-xs font-semibold text-red-700 hover:bg-red-50" @click="form.option_groups.splice(groupIndex, 1)">Remove group</button>
                        </div>
                        <div v-for="(option, optionIndex) in group.options" :key="option.id" class="grid gap-2 sm:grid-cols-[1fr_150px_auto]">
                            <label class="block text-xs font-medium text-neutral-600">Choice<input v-model="option.name" required maxlength="60" placeholder="Chicken" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm font-normal text-neutral-900" /></label>
                            <label class="block text-xs font-medium text-neutral-600">Extra price (GHS)<input v-model="option.price_ghs" type="number" min="0" step="0.01" required class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-right font-mono text-sm text-neutral-900" /></label>
                            <button v-if="group.options.length > 1" type="button" class="mt-5 min-h-10 rounded-md px-2 text-xs font-semibold text-neutral-600 hover:bg-white" :aria-label="`Remove choice ${optionIndex + 1}`" @click="group.options.splice(optionIndex, 1)">Remove</button>
                        </div>
                        <button type="button" class="text-xs font-semibold text-emerald-800 hover:underline" @click="addOption(group)">Add another choice</button>
                    </div>
                    <p v-if="form.errors.option_groups" class="text-sm text-red-700">{{ form.errors.option_groups }}</p>
                </section>
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
