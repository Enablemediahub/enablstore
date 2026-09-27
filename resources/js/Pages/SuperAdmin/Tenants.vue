<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import SuperAdminSidePanel from '@/Components/SuperAdminSidePanel.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, Plus, Search, Settings, Store, UserCheck, Users, UserX, WalletCards, X } from '@lucide/vue';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    tenants: { data: Array<{ id: string; subscriber_code: string | null; name: string; slug: string; email: string | null; phone: string | null; status: string; plan: string | null; subscription_status: string | null }>; links: Array<{ url: string | null; label: string; active: boolean }>; current_page: number; last_page: number };
    filters: { search: string; feature: 'pos' | 'online_store' | null };
    enrol: boolean;
    status?: string | null;
    plans: Array<{ id: number; name: string; features: string[]; price_minor: number; currency: string; billing_interval_months: number }>;
    metrics: { subscriber_count: number; active_count: number; suspended_count: number; active_subscriptions: number; workspace_users: number };
}>();

const search = ref(props.filters.search);
const enrolling = ref(props.enrol);
const accessLabel = props.filters.feature === 'pos' ? 'POS access' : props.filters.feature === 'online_store' ? 'Online Store access' : props.filters.feature === 'restaurant_foodstore' ? 'FoodStore access' : null;
const portalOptions = [
    { key: 'online_store', label: 'Online Store' },
    { key: 'pos', label: 'Point of Sale' },
    { key: 'restaurant_foodstore', label: 'FoodStore' },
];
const enrollment = useForm({ name: '', username: '', email: '', password: '', business_name: '', plan_id: props.plans[0]?.id ?? '', features: props.plans[0]?.features.filter((feature) => portalOptions.some((option) => option.key === feature)) ?? [] });
const enrollmentErrors = computed(() => [...new Set(Object.values(enrollment.errors).filter((message): message is string => typeof message === 'string' && message !== ''))]);
watch(() => enrollment.plan_id, (planId) => {
    const plan = props.plans.find((item) => item.id === Number(planId));
    if (plan) enrollment.features = plan.features.filter((feature) => portalOptions.some((option) => option.key === feature));
});
let searchTimeout: ReturnType<typeof setTimeout> | undefined;
const submitSearch = (): void => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = undefined;
    router.get(route('super-admin.tenants.index'), {
        search: search.value.trim() || undefined,
        feature: props.filters.feature ?? undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
watch(search, () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(submitSearch, 300);
});
const clearSearch = (): void => { search.value = ''; };
const enrol = (): void => enrollment.post(route('super-admin.users.store'), { onSuccess: () => { enrollment.reset(); enrolling.value = false; } });
const openEnrollment = async (): Promise<void> => {
    enrolling.value = true;
    await nextTick();
    document.getElementById('enrol')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

onMounted(() => {
    if (props.enrol) {
        void openEnrollment();
    }
});

onUnmounted(() => {
    if (searchTimeout) clearTimeout(searchTimeout);
});
</script>

<template>
    <Head title="Subscribers" />
    <main class="flex min-h-screen bg-neutral-50 text-neutral-900"><SuperAdminSidePanel current="tenants" /><section class="min-w-0 flex-1 px-5 py-8 sm:px-10"><div v-if="status" role="status" class="mx-auto mb-6 max-w-5xl rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">{{ status }}</div><div class="mx-auto max-w-5xl space-y-6">
        <div v-if="enrollmentErrors.length" role="alert" class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><p class="font-bold">Subscriber could not be created. Check the following:</p><ul class="mt-2 list-inside list-disc space-y-1"><li v-for="message in enrollmentErrors" :key="message">{{ message }}</li></ul></div>
        <header class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm font-semibold text-[#e21b23]">{{ accessLabel ?? 'Subscriber management' }}</p><h1 class="mt-1 text-3xl font-black">{{ accessLabel ?? 'Subscribers' }}</h1><p class="mt-2 max-w-3xl text-neutral-500">{{ accessLabel ? `Subscribers whose active plan includes ${accessLabel}. Select a subscriber to view their main administrator, other admins, and cashiers.` : 'Enrol each business once. Its first login is the main administrator; their team members are shown within that subscriber.' }}</p></div><button v-if="!accessLabel" type="button" class="inline-flex min-h-11 items-center gap-2 rounded-md bg-[#e21b23] px-4 text-sm font-bold text-white hover:bg-[#b9151b]" @click="enrolling ? enrolling = false : openEnrollment()"><Plus :size="17" aria-hidden="true" /> Add subscriber</button></header>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5"><EnCard><Users :size="20" class="text-[#e21b23]" /><p class="mt-3 text-sm text-neutral-500">Subscribers</p><p class="mt-1 text-2xl font-bold">{{ metrics.subscriber_count }}</p></EnCard><EnCard><UserCheck :size="20" class="text-green-600" /><p class="mt-3 text-sm text-neutral-500">Active</p><p class="mt-1 text-2xl font-bold">{{ metrics.active_count }}</p></EnCard><EnCard><UserX :size="20" class="text-amber-600" /><p class="mt-3 text-sm text-neutral-500">Suspended</p><p class="mt-1 text-2xl font-bold">{{ metrics.suspended_count }}</p></EnCard><EnCard><WalletCards :size="20" class="text-blue-600" /><p class="mt-3 text-sm text-neutral-500">Active plans</p><p class="mt-1 text-2xl font-bold">{{ metrics.active_subscriptions }}</p></EnCard><EnCard><Users :size="20" class="text-violet-600" /><p class="mt-3 text-sm text-neutral-500">All team members</p><p class="mt-1 text-2xl font-bold">{{ metrics.workspace_users }}</p></EnCard></div>
        <EnCard v-if="!accessLabel" id="enrol"><div v-if="!enrolling" class="flex items-center justify-between gap-4"><div><h2 class="text-lg font-bold">Subscriber setup</h2><p class="mt-1 text-sm text-neutral-500">Create a business workspace, assign a subscription, and set up its main administrator.</p></div><ChevronDown :size="20" class="text-neutral-400" aria-hidden="true" /></div><form v-if="enrolling" class="space-y-4" @submit.prevent="enrol"><div class="border-b border-neutral-200 pb-4"><h2 class="text-lg font-bold">Enrol a subscriber</h2><p class="mt-1 text-sm text-neutral-500">This creates the workspace, provisions its database and portals, assigns the plan, and creates the main administrator.</p></div><EnInput id="subscriber-business" v-model="enrollment.business_name" label="Business name" required /><EnInput id="subscriber-admin" v-model="enrollment.name" label="Main administrator name" required /><EnInput id="subscriber-username" v-model="enrollment.username" label="Administrator username suffix" placeholder="e.g. ama (becomes ES001-ama)" required /><EnInput id="subscriber-email" v-model="enrollment.email" type="email" label="Administrator email (optional)" /><EnInput id="subscriber-password" v-model="enrollment.password" type="password" label="Administrator login password" required /><label class="block text-sm font-medium text-neutral-700">Subscription plan<select v-model="enrollment.plan_id" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option></select></label><fieldset class="rounded-md border border-neutral-200 p-4"><legend class="px-1 text-sm font-semibold text-neutral-700">Portal access</legend><label v-for="portal in portalOptions" :key="portal.key" class="flex items-center gap-2 py-1.5 text-sm text-neutral-700"><input v-model="enrollment.features" type="checkbox" :value="portal.key" class="rounded border-neutral-300 text-[#e21b23] focus:ring-[#e21b23]" />{{ portal.label }}</label></fieldset><p v-if="enrollment.errors.username || enrollment.errors.email || enrollment.errors.plan_id || enrollment.errors.features" class="text-sm text-red-700">{{ enrollment.errors.username || enrollment.errors.email || enrollment.errors.plan_id || enrollment.errors.features }}</p><div class="flex justify-end"><button type="submit" class="min-h-11 rounded-md bg-[#e21b23] px-5 text-sm font-bold text-white disabled:opacity-60" :disabled="enrollment.processing">Create subscriber</button></div></form></EnCard>
        <EnCard><form class="flex flex-col gap-3" @submit.prevent="submitSearch"><label class="relative"><span class="sr-only">Search subscribers</span><Search :size="18" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" /><input v-model="search" type="search" class="min-h-11 w-full rounded-md border border-neutral-300 py-2 pr-10 pl-10 text-sm" placeholder="Search by business, contact, or store URL" /><button v-if="search" type="button" class="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-neutral-400" aria-label="Clear search" @click="clearSearch"><X :size="17" /></button></label><button type="submit" class="min-h-11 rounded-md bg-[#171717] px-5 text-sm font-semibold text-white">Search subscribers</button></form></EnCard>
        <EnCard><div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="text-lg font-bold">Subscriber directory</h2><p class="mt-1 text-sm text-neutral-500">{{ tenants.data.length }} subscriber{{ tenants.data.length === 1 ? '' : 's' }} on this page</p></div></div><div v-if="tenants.data.length" class="mt-5 overflow-x-auto"><table class="w-full min-w-[1180px] text-left text-sm"><thead class="border-b border-neutral-200 text-xs font-semibold tracking-wide text-neutral-500 uppercase"><tr><th class="px-3 py-3">Code</th><th class="px-3 py-3">Subscriber</th><th class="px-3 py-3">Contact</th><th class="px-3 py-3">Plan</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Tenant URLs</th><th class="px-3 py-3 text-right">Actions</th></tr></thead><tbody><tr v-for="tenant in tenants.data" :key="tenant.id" class="border-b border-neutral-100 last:border-0"><td class="px-3 py-4"><span class="rounded bg-neutral-100 px-2 py-1 font-mono text-xs font-bold">{{ tenant.subscriber_code }}</span></td><td class="px-3 py-4"><p class="font-semibold">{{ tenant.name }}</p><p class="mt-1 font-mono text-xs text-neutral-500">/onlinestore/{{ tenant.slug }}</p></td><td class="px-3 py-4"><p class="text-neutral-700">{{ tenant.phone || 'No phone number' }}</p><p class="mt-1 text-xs text-neutral-500">{{ tenant.email || 'No email address' }}</p></td><td class="px-3 py-4"><p class="font-medium">{{ tenant.plan || 'Not enrolled' }}</p><p v-if="tenant.subscription_status" class="mt-1 text-xs capitalize text-neutral-500">{{ tenant.subscription_status.replace('_', ' ') }}</p></td><td class="px-3 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize" :class="tenant.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'">{{ tenant.status }}</span></td><td class="px-3 py-4"><a :href="route('tenant.home', { tenant: tenant.id })" target="_blank" class="block font-mono text-xs text-[#e21b23] hover:underline">Online: {{ route('tenant.home', { tenant: tenant.id }) }}</a><a :href="route('tenant.pos', { tenant: tenant.id })" target="_blank" class="mt-1 block font-mono text-xs text-blue-700 hover:underline">POS: {{ route('tenant.pos', { tenant: tenant.id }) }}</a><a :href="route('tenant.foodstore.index', { tenant: tenant.id })" target="_blank" class="mt-1 block font-mono text-xs text-emerald-700 hover:underline">FoodStore: {{ route('tenant.foodstore.index', { tenant: tenant.id }) }}</a></td><td class="px-3 py-4"><div class="flex justify-end gap-2"><a :href="route('tenant.home', { tenant: tenant.id })" target="_blank" class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-neutral-300 px-3 text-xs font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><Store :size="15" /> Store</a><Link :href="route('super-admin.tenants.show', { tenant: tenant.id })" class="inline-flex min-h-9 items-center gap-1.5 rounded-md bg-[#e21b23] px-3 text-xs font-bold text-white hover:bg-[#b9151b]"><Settings :size="15" /> Manage</Link></div></td></tr></tbody></table></div><p v-else class="mt-5 rounded-md bg-neutral-50 p-5 text-sm text-neutral-500">No subscribers match this search.</p></EnCard>
        <div v-if="tenants.last_page > 1" class="flex flex-wrap justify-center gap-2 pt-2"><button v-for="link in tenants.links" :key="link.label" type="button" class="rounded-md border px-3 py-2 text-sm" :class="link.active ? 'border-[#e21b23] bg-[#e21b23] text-white' : 'border-neutral-200 bg-white text-neutral-700'" :disabled="!link.url" @click="link.url && router.visit(link.url)"><span v-html="link.label" /></button></div>
    </div></section></main>
</template>
