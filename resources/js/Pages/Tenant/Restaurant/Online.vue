<script setup lang="ts">
import EnEmptyState from '@/Components/EnEmptyState.vue';
import WhatsAppContactButton from '@/Components/WhatsAppContactButton.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Image, LayoutGrid, MessageCircle, Minus, Plus, Rows3, Search, ShoppingCart, Sparkles, Trash2, Utensils, X } from '@lucide/vue';
import { computed, ref } from 'vue';

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

type SelectedOption = { id: string; quantity: number };
type CartItem = MenuItem & { cartLineId: string; quantity: number; selectedOptions: SelectedOption[] };
type DisplayMode = 'list' | 'grid' | 'thumb';

const props = defineProps<{
    tenant: string;
    restaurantName: string;
    whatsappPhone: string | null;
    heroImageUrl: string | null;
    menuItems: MenuItem[];
    status?: string | null;
}>();

const page = usePage();
const hasWhatsAppOrderAccess = computed(() => ((page.props.tenantFeatures as string[] | undefined) ?? []).includes('whatsapp_orders'));
const cart = ref<CartItem[]>([]);
const searchQuery = ref('');
const selectedCategory = ref('All');
const displayMode = ref<DisplayMode>('grid');
const selectedAllergies = ref<string[]>([]);
const otherAllergy = ref('');
const allergyChoices = ['Peanuts', 'Tree nuts', 'Dairy', 'Eggs', 'Fish', 'Shellfish', 'Soy', 'Gluten'];
const form = useForm({
    customer_name: '',
    customer_phone: '',
    notes: '',
    channel: 'website' as 'website' | 'whatsapp',
    items: [] as Array<{ menu_item_id: number; quantity: number; selected_options: SelectedOption[] }>,
});

const categories = computed<string[]>(() => ['All', ...Array.from(new Set(props.menuItems.map((item) => item.category).filter(Boolean)))]);
const filteredItems = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    return props.menuItems.filter((item) => {
        const matchesCategory = selectedCategory.value === 'All' || item.category === selectedCategory.value;
        const haystack = `${item.name} ${item.category} ${item.description ?? ''}`.toLowerCase();
        const matchesQuery = query === '' || haystack.includes(query);
        return matchesCategory && matchesQuery;
    });
});
const optionQuantity = (item: CartItem, optionId: string): number => item.selectedOptions.find((option) => option.id === optionId)?.quantity ?? 0;
const selectedOptionPrice = (item: CartItem): number => item.option_groups
    .flatMap((group) => group.options)
    .reduce((total, option) => total + option.price_minor * optionQuantity(item, option.id), 0);
const itemUnitPrice = (item: CartItem): number => item.price_minor + selectedOptionPrice(item);
const cartOptionsValid = computed(() => cart.value.every((item) => item.option_groups.every((group) => !group.required || group.options.some((option) => optionQuantity(item, option.id) > 0))));
const totalMinor = computed(() => cart.value.reduce((total, item) => total + itemUnitPrice(item) * item.quantity, 0));
const canSubmit = computed(() => cart.value.length > 0 && cartOptionsValid.value && form.customer_name.trim().length > 0 && form.customer_phone.trim().length > 0);
const heroImage = computed(() => props.heroImageUrl ?? props.menuItems.find((item) => item.image_url)?.image_url ?? '/images/storefront/hero-default.svg');
const hasItems = computed(() => filteredItems.value.length > 0);
const allergyNote = computed(() => [...selectedAllergies.value, ...(otherAllergy.value.trim() ? [otherAllergy.value.trim()] : [])].join(', '));
const orderNotes = computed(() => [allergyNote.value ? `ALLERGY ALERT: ${allergyNote.value}` : '', form.notes.trim()].filter(Boolean).join('\n'));
const whatsappOrderUrl = computed(() => {
    if (!hasWhatsAppOrderAccess.value) return null;
    const phone = (props.whatsappPhone ?? '').replace(/\D/g, '');
    if (phone.length < 8 || !canSubmit.value) return null;

    const lines = cart.value.map((item, index) => {
        const options = item.option_groups.flatMap((group) => group.options
            .filter((option) => optionQuantity(item, option.id) > 0)
            .map((option) => `  ${group.name}: ${optionQuantity(item, option.id)} x ${option.name} (+${formatPrice(option.price_minor)} each) = ${formatPrice(option.price_minor * optionQuantity(item, option.id))}`));
        return [
            `*${index + 1}. ${item.quantity} x ${item.name}*`,
            `  Food: ${formatPrice(item.price_minor)} each`,
            ...options,
            `  Unit total: ${formatPrice(itemUnitPrice(item))}`,
            `  Line total: ${formatPrice(itemUnitPrice(item) * item.quantity)}`,
        ].join('\n');
    });
    const message = [
        `*FoodStore order - ${props.restaurantName}*`,
        '------------------------------',
        ...lines,
        '------------------------------',
        `*Total: ${formatPrice(totalMinor.value)}*`,
        `Customer: ${form.customer_name.trim()}`,
        `Phone: ${form.customer_phone.trim()}`,
        orderNotes.value ? `Notes: ${orderNotes.value}` : '',
    ].filter(Boolean).join('\n');

    return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
});

const formatPrice = (minor: number): string => new Intl.NumberFormat('en-GH', {
    style: 'currency',
    currency: 'GHS',
}).format(minor / 100);

const addItem = (item: MenuItem): void => {
    if (!item.is_available) return;
    const existing = cart.value.find((line) => line.id === item.id && line.selectedOptions.length === 0);
    if (existing) existing.quantity += 1;
    else cart.value.push({ ...item, cartLineId: crypto.randomUUID(), quantity: 1, selectedOptions: [] });
};

const adjustQuantity = (cartLineId: string, amount: number): void => {
    const line = cart.value.find((item) => item.cartLineId === cartLineId);
    if (!line) return;
    line.quantity += amount;
    if (line.quantity <= 0) cart.value = cart.value.filter((item) => item.cartLineId !== cartLineId);
};

const setSingleOption = (item: CartItem, group: MenuItem['option_groups'][number], optionId: string): void => {
    const groupOptionIds = group.options.map((option) => option.id);
    item.selectedOptions = item.selectedOptions.filter((selection) => !groupOptionIds.includes(selection.id));
    if (optionId) item.selectedOptions.push({ id: optionId, quantity: 1 });
};

const toggleMultipleOption = (item: CartItem, optionId: string, checked: boolean): void => {
    const quantity = checked ? 1 : 0;
    setOptionQuantity(item, optionId, quantity);
};

const setOptionQuantity = (item: CartItem, optionId: string, quantity: number): void => {
    const boundedQuantity = Math.max(0, Math.min(99, Math.trunc(quantity)));
    const existing = item.selectedOptions.find((selection) => selection.id === optionId);
    if (boundedQuantity === 0) {
        item.selectedOptions = item.selectedOptions.filter((selection) => selection.id !== optionId);
    } else if (existing) {
        existing.quantity = boundedQuantity;
    } else {
        item.selectedOptions.push({ id: optionId, quantity: boundedQuantity });
    }
};

const clearSearch = (): void => {
    searchQuery.value = '';
    selectedCategory.value = 'All';
};

const submitOrder = (channel: 'website' | 'whatsapp' = 'website'): void => {
    if (!canSubmit.value) return;
    const whatsappUrl = channel === 'whatsapp' ? whatsappOrderUrl.value : null;
    if (channel === 'whatsapp' && !whatsappUrl) return;
    const whatsappWindow = channel === 'whatsapp' ? window.open('about:blank', '_blank') : null;

    form.items = cart.value.map((item) => ({ menu_item_id: item.id, quantity: item.quantity, selected_options: item.selectedOptions }));
    form.notes = orderNotes.value;
    form.channel = channel;
    form.post(route('tenant.foodstore.online.orders.store', { tenant: props.tenant }), {
        preserveScroll: true,
        onSuccess: () => {
            if (whatsappUrl) {
                if (whatsappWindow) {
                    whatsappWindow.opener = null;
                    whatsappWindow.location.href = whatsappUrl;
                } else {
                    window.location.href = whatsappUrl;
                }
            }
            cart.value = [];
            form.customer_name = '';
            form.customer_phone = '';
            form.notes = '';
            selectedAllergies.value = [];
            otherAllergy.value = '';
            form.channel = 'website';
        },
        onError: () => whatsappWindow?.close(),
    });
};
</script>

<template>
    <Head :title="`FoodStore Online - ${restaurantName}`" />
    <main class="min-h-screen bg-[#f7f5f1] text-neutral-900">
        <div class="mx-auto min-h-screen max-w-[1500px] px-4 py-5 sm:px-8 sm:py-8">
            <header class="relative isolate mb-7 flex min-h-56 items-end overflow-hidden rounded-[28px] bg-emerald-950 bg-cover bg-center px-5 py-6 text-white shadow-[0_18px_56px_rgba(14,100,70,0.18)] sm:min-h-72 sm:px-9 sm:py-8" :style="{ backgroundImage: `url('${heroImage}')` }">
                <div class="absolute inset-0 -z-10 bg-emerald-950/75" aria-hidden="true" />
                <div class="flex items-end gap-4 sm:gap-6">
                    <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-white/80 bg-white p-2 shadow-lg ring-4 ring-emerald-300/30 sm:size-20">
                        <img src="/images/Enablstore.png" alt="Enablstore" class="h-full w-full object-contain" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-100">FoodStore Online</p>
                        <h1 class="mt-1 truncate text-3xl font-black sm:text-5xl">{{ restaurantName }}</h1>
                        <p class="mt-2 text-sm text-white/85">Choose your food and send an order directly to the restaurant.</p>
                    </div>
                </div>
            </header>

            <p v-if="status" role="status" class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ status }}</p>

            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
                <section id="menu" class="min-w-0">
                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-800">Menu</p>
                            <h2 class="mt-1 text-2xl font-black">Order online</h2>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 rounded-xl border border-neutral-200 bg-white/90 p-1.5 shadow-sm">
                            <button type="button" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-2 text-xs font-bold transition" :class="displayMode === 'list' ? 'bg-emerald-700 text-white' : 'text-neutral-600 hover:bg-neutral-100'" @click="displayMode = 'list'" aria-label="List view">
                                <Rows3 :size="15" aria-hidden="true" /> List
                            </button>
                            <button type="button" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-2 text-xs font-bold transition" :class="displayMode === 'grid' ? 'bg-emerald-700 text-white' : 'text-neutral-600 hover:bg-neutral-100'" @click="displayMode = 'grid'" aria-label="Grid view">
                                <LayoutGrid :size="15" aria-hidden="true" /> Grid
                            </button>
                            <button type="button" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-2 text-xs font-bold transition" :class="displayMode === 'thumb' ? 'bg-emerald-700 text-white' : 'text-neutral-600 hover:bg-neutral-100'" @click="displayMode = 'thumb'" aria-label="Thumbnail view">
                                <Image :size="15" aria-hidden="true" /> Thumb
                            </button>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-neutral-200 bg-white p-3 shadow-sm sm:p-4">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <label class="relative block w-full md:max-w-md">
                                <span class="sr-only">Search menu</span>
                                <Search :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500" aria-hidden="true" />
                                <input v-model="searchQuery" type="search" placeholder="Search meals, drinks, smoothies..." class="h-11 w-full rounded-xl border border-neutral-200 bg-neutral-50 pl-9 pr-3 text-sm text-neutral-800 placeholder:text-neutral-400 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100" />
                            </label>
                            <button v-if="searchQuery || selectedCategory !== 'All'" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm font-semibold text-neutral-700 hover:border-neutral-300" @click="clearSearch">
                                <X :size="15" aria-hidden="true" /> Clear
                            </button>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button v-for="category in categories" :key="category" type="button" class="rounded-full px-3 py-1.5 text-xs font-bold transition" :class="selectedCategory === category ? 'bg-emerald-700 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200'" @click="selectedCategory = category">
                                {{ category }}
                            </button>
                        </div>
                    </div>

                    <div v-if="menuItems.length" class="mt-5">
                        <div v-if="!hasItems" class="rounded-[22px] border border-dashed border-emerald-200 bg-emerald-50 px-5 py-8 text-center text-sm text-emerald-900">
                            No menu items match “{{ searchQuery }}” in {{ selectedCategory === 'All' ? 'all categories' : selectedCategory }}.
                        </div>

                        <div v-else-if="displayMode === 'list'" class="space-y-3">
                            <article v-for="item in filteredItems" :key="item.id" class="flex flex-col gap-3 rounded-[22px] border border-neutral-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center">
                                <div class="h-24 w-full overflow-hidden rounded-[18px] bg-emerald-50 sm:w-32">
                                    <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                                    <div v-else class="grid h-full place-items-center text-emerald-700"><Utensils :size="26" aria-hidden="true" /></div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-800">{{ item.category }}</span>
                                        <span v-if="!item.is_available" class="rounded-full bg-neutral-200 px-2 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-neutral-600">Unavailable</span>
                                    </div>
                                    <h3 class="mt-2 text-lg font-black text-neutral-900">{{ item.name }}</h3>
                                    <p v-if="item.description" class="mt-1 text-sm text-neutral-600">{{ item.description }}</p>
                                </div>
                                <div class="flex items-center gap-3 sm:justify-end">
                                    <div class="text-left sm:text-right">
                                        <p class="font-mono text-lg font-black text-neutral-900">{{ formatPrice(item.price_minor) }}</p>
                                        <p class="text-xs text-neutral-500">per {{ item.unit_label }}</p>
                                    </div>
                                    <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!item.is_available" @click="addItem(item)">
                                        <Plus :size="16" aria-hidden="true" /> Add
                                    </button>
                                </div>
                            </article>
                        </div>

                        <div v-else-if="displayMode === 'grid'" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <article v-for="item in filteredItems" :key="item.id" class="overflow-hidden rounded-[22px] border border-neutral-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <button type="button" class="flex h-full w-full flex-col items-start p-4 text-left disabled:cursor-not-allowed disabled:opacity-60" :disabled="!item.is_available" @click="addItem(item)">
                                    <div class="mb-3 aspect-[4/3] w-full overflow-hidden rounded-[18px] bg-emerald-50">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                                        <div v-else class="grid h-full place-items-center text-emerald-700"><Utensils :size="32" aria-hidden="true" /></div>
                                    </div>
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-800">{{ item.category }}</span>
                                        <span v-if="!item.is_available" class="rounded-full bg-neutral-200 px-2 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-neutral-600">Unavailable</span>
                                    </div>
                                    <span class="mt-3 text-lg font-black text-neutral-900">{{ item.name }}</span>
                                    <span v-if="item.description" class="mt-1 line-clamp-2 text-sm text-neutral-600">{{ item.description }}</span>
                                    <span class="mt-4 flex w-full items-center justify-between gap-2">
                                        <strong class="font-mono text-base font-black text-neutral-900">{{ formatPrice(item.price_minor) }}</strong>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1.5 text-xs font-bold text-emerald-800"><Plus :size="15" aria-hidden="true" /> Add</span>
                                    </span>
                                </button>
                            </article>
                        </div>

                        <div v-else class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <article v-for="item in filteredItems" :key="item.id" class="rounded-[22px] border border-neutral-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <button type="button" class="flex h-full w-full flex-col items-start text-left disabled:cursor-not-allowed disabled:opacity-60" :disabled="!item.is_available" @click="addItem(item)">
                                    <div class="mb-3 aspect-square w-full overflow-hidden rounded-[18px] bg-emerald-50">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                                        <div v-else class="grid h-full place-items-center text-emerald-700"><Sparkles :size="28" aria-hidden="true" /></div>
                                    </div>
                                    <div class="flex w-full items-center justify-between gap-2">
                                        <span class="text-sm font-black text-neutral-900">{{ item.name }}</span>
                                        <span class="font-mono text-sm font-black text-neutral-900">{{ formatPrice(item.price_minor) }}</span>
                                    </div>
                                    <span class="mt-1 text-xs text-neutral-500">{{ item.category }} · {{ item.unit_label }}</span>
                                    <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white"><Plus :size="14" aria-hidden="true" /> Quick add</span>
                                </button>
                            </article>
                        </div>
                    </div>

                    <EnEmptyState v-else title="Menu coming soon" description="This restaurant has not added online menu items yet." />
                </section>

                <aside class="lg:sticky lg:top-5">
                    <section class="rounded-[28px] border border-emerald-800 bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-800 p-5 text-white shadow-[0_22px_60px_rgba(11,75,47,0.28)] sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <ShoppingCart :size="19" class="text-emerald-200" aria-hidden="true" />
                                <h2 class="font-black">Your order</h2>
                            </div>
                            <span class="rounded-full border border-white/15 bg-white/10 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-white/70">{{ cart.reduce((count, item) => count + item.quantity, 0) }} items</span>
                        </div>

                        <div v-if="cart.length" class="mt-4 space-y-3">
                            <div v-for="item in cart" :key="item.cartLineId" class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                <div class="flex items-start gap-3">
                                    <div class="h-12 w-12 overflow-hidden rounded-xl bg-white/10">
                                        <img v-if="item.image_url" :src="item.image_url" :alt="item.name" class="h-full w-full object-cover" />
                                        <div v-else class="grid h-full place-items-center text-white/80"><Utensils :size="18" aria-hidden="true" /></div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="truncate text-sm font-bold">{{ item.name }}</p>
                                                <p class="mt-1 text-[11px] text-white/65">{{ formatPrice(item.price_minor) }} · {{ item.unit_label }}</p>
                                            </div>
                                            <button type="button" class="grid size-7 place-items-center rounded-md text-white/60 hover:bg-white/10 hover:text-white" :aria-label="`Remove ${item.name} from order`" @click="cart = cart.filter((line) => line.cartLineId !== item.cartLineId)">
                                                <Trash2 :size="14" aria-hidden="true" />
                                            </button>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="grid size-8 place-items-center rounded-md border border-white/20 hover:bg-white/10" :aria-label="`Remove one ${item.name}`" @click="adjustQuantity(item.cartLineId, -1)">
                                                    <Minus :size="14" aria-hidden="true" />
                                                </button>
                                                <span class="w-6 text-center text-sm font-bold">{{ item.quantity }}</span>
                                                <button type="button" class="grid size-8 place-items-center rounded-md border border-white/20 hover:bg-white/10" :aria-label="`Add one ${item.name}`" @click="adjustQuantity(item.cartLineId, 1)">
                                                    <Plus :size="14" aria-hidden="true" />
                                                </button>
                                            </div>
                                            <strong class="font-mono text-sm">{{ formatPrice(itemUnitPrice(item) * item.quantity) }}</strong>
                                        </div>
                                        <div v-if="item.option_groups.length" class="mt-3 space-y-2 border-t border-white/10 pt-3">
                                            <fieldset v-for="group in item.option_groups" :key="group.id" class="rounded-md border border-white/10 bg-black/10 p-2.5">
                                                <legend class="px-1 text-[11px] font-bold text-white/80">{{ group.name }}{{ group.required ? ' · required' : ' · optional' }}</legend>
                                                <select v-if="!group.multiple" :value="group.options.find((option) => optionQuantity(item, option.id) > 0)?.id ?? ''" class="min-h-9 w-full rounded-md border border-neutral-300 bg-white px-2 text-xs text-neutral-900" @change="setSingleOption(item, group, ($event.target as HTMLSelectElement).value)">
                                                    <option value="">{{ group.required ? 'Choose one' : `No ${group.name.toLowerCase()}` }}</option>
                                                    <option v-for="option in group.options" :key="option.id" :value="option.id">{{ option.name }} · {{ formatPrice(option.price_minor) }}</option>
                                                </select>
                                                <label v-if="!group.multiple && group.options.some((option) => optionQuantity(item, option.id) > 0)" class="mt-2 flex items-center justify-between gap-3 text-xs text-white/90">Portions<input type="number" min="1" max="99" :value="optionQuantity(item, group.options.find((option) => optionQuantity(item, option.id) > 0)?.id ?? '')" class="min-h-8 w-20 rounded-md border border-neutral-300 bg-white px-2 text-right text-neutral-900" @input="setOptionQuantity(item, group.options.find((option) => optionQuantity(item, option.id) > 0)?.id ?? '', Number(($event.target as HTMLInputElement).value))" /></label>
                                                <div v-if="group.multiple" class="mt-1 space-y-1">
                                                    <div v-for="option in group.options" :key="option.id" class="flex min-h-9 items-center justify-between gap-2 text-xs text-white/90">
                                                        <label class="flex items-center gap-2"><input type="checkbox" :checked="optionQuantity(item, option.id) > 0" class="rounded border-white/40 text-emerald-700" @change="toggleMultipleOption(item, option.id, ($event.target as HTMLInputElement).checked)" />{{ option.name }} · {{ formatPrice(option.price_minor) }} each</label>
                                                        <input v-if="optionQuantity(item, option.id) > 0" type="number" min="1" max="99" :value="optionQuantity(item, option.id)" :aria-label="`${option.name} portions`" class="min-h-8 w-16 rounded-md border border-neutral-300 bg-white px-2 text-right text-neutral-900" @input="setOptionQuantity(item, option.id, Number(($event.target as HTMLInputElement).value))" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mt-4 rounded-[18px] border border-dashed border-white/30 bg-white/5 px-4 py-7 text-center text-sm text-white/70">Add food from the menu to begin.</p>

                        <div class="mt-5 border-t border-white/15 pt-4">
                            <div class="flex items-center justify-between text-lg font-black">
                                <span>Total</span>
                                <span class="font-mono">{{ formatPrice(totalMinor) }}</span>
                            </div>

                            <form class="mt-4 space-y-3" @submit.prevent="submitOrder('website')">
                                <label class="block text-xs font-bold text-white/85">Your name
                                    <input v-model="form.customer_name" required maxlength="120" autocomplete="name" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm font-normal text-neutral-900" />
                                </label>
                                <label class="block text-xs font-bold text-white/85">Phone number
                                    <input v-model="form.customer_phone" required maxlength="40" type="tel" autocomplete="tel" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm font-normal text-neutral-900" />
                                </label>
                                <label class="block text-xs font-bold text-white/85">Order notes (optional)
                                    <textarea v-model="form.notes" maxlength="300" rows="2" class="mt-1.5 w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-sm font-normal text-neutral-900" />
                                </label>
                                <fieldset class="rounded-md border border-white/15 bg-white/5 p-3">
                                    <legend class="px-1 text-xs font-bold text-white/90">Food allergies or dietary alerts</legend>
                                    <p class="mb-2 text-[11px] text-white/65">Selected allergies are highlighted for the kitchen.</p>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <label v-for="allergy in allergyChoices" :key="allergy" class="flex items-center gap-2 text-xs text-white/90"><input v-model="selectedAllergies" type="checkbox" :value="allergy" class="rounded border-white/40 text-emerald-700" />{{ allergy }}</label>
                                    </div>
                                    <input v-model="otherAllergy" maxlength="100" placeholder="Other allergy or dietary warning" class="mt-2 min-h-9 w-full rounded-md border border-neutral-300 bg-white px-2.5 text-xs text-neutral-900" />
                                </fieldset>
                                <p v-if="form.errors.items || form.errors.customer_name || form.errors.customer_phone" class="text-sm font-semibold text-red-100">{{ form.errors.items || form.errors.customer_name || form.errors.customer_phone }}</p>
                                <p v-if="form.errors.notes" class="text-sm font-semibold text-red-100">{{ form.errors.notes }}</p>
                                <p v-if="!cartOptionsValid && cart.length" class="text-xs font-semibold text-amber-200">Choose an option in each required group before sending the order.</p>
                                <button type="submit" class="min-h-11 w-full rounded-xl bg-emerald-300 px-4 text-sm font-black text-emerald-950 hover:bg-emerald-200 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!canSubmit || form.processing">
                                    {{ form.processing ? 'Sending order...' : 'Send order' }}
                                </button>
                                <button v-if="whatsappOrderUrl" type="button" class="flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 text-sm font-bold text-emerald-900 hover:bg-emerald-50 disabled:opacity-60" :disabled="form.processing" @click="submitOrder('whatsapp')"><MessageCircle :size="17" aria-hidden="true" />{{ form.processing && form.channel === 'whatsapp' ? 'Saving order...' : 'Send order via WhatsApp' }}</button>
                                <p class="text-center text-xs text-white/65">The restaurant will contact you to confirm your order.</p>
                            </form>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
        <WhatsAppContactButton :phone="whatsappPhone" :message="`Hello ${restaurantName}, I have a question about your menu.`" />
    </main>
</template>
