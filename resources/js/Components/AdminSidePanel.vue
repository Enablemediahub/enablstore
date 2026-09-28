<script setup lang="ts">
import { ArrowLeft, BarChart3, ClipboardList, FileText, LayoutDashboard, LogOut, Package, Settings, ShoppingCart, Store, Tags, Truck, Utensils, Users, Wallet, X } from '@lucide/vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type PanelContext = 'workspace' | 'storefront' | 'pos' | 'foodstore' | 'foodstore-online' | 'food-menu' | 'kitchen-orders' | 'products' | 'categories' | 'settings' | 'analytics' | 'suppliers' | 'reports' | 'sales-expenses' | 'audit' | 'team';

const props = withDefaults(
    defineProps<{
        tenant: string;
        mobileOpen?: boolean;
        current?: PanelContext;
        overlayMode?: boolean;
    }>(),
    {
        mobileOpen: false,
        current: 'products',
        overlayMode: false,
    },
);

const emit = defineEmits<{
    close: [];
}>();

const logoutForm = useForm({});
const page = usePage();
const hasSalesExpensesAccess = computed(() => ((page.props.tenantFeatures as string[] | undefined) ?? []).includes('sales_expenses'));
const hasAuditLogAccess = computed(() => ((page.props.tenantFeatures as string[] | undefined) ?? []).includes('audit_log'));
const isFoodStoreTheme = computed(() => props.current === 'foodstore' || props.current === 'food-menu');
const goBack = (): void => {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    window.location.assign(route('tenant.dashboard', { tenant: props.tenant }));
};
const logout = (): void => {
    logoutForm.post(route('logout'));
};

const groups = computed(() => [
    {
        label: 'Workspace',
        links: [
            { label: 'Admin dashboard', href: route('tenant.dashboard', { tenant: props.tenant }), icon: LayoutDashboard, active: props.current === 'workspace' },
            { label: 'Storefront', href: route('tenant.home', { tenant: props.tenant }), icon: Store, active: props.current === 'storefront' },
            { label: 'Point of sale', href: route('tenant.pos', { tenant: props.tenant }), icon: ShoppingCart, active: props.current === 'pos' },
            { label: 'FoodStore POS', href: route('tenant.foodstore.index', { tenant: props.tenant }), icon: Utensils, active: props.current === 'foodstore' },
            { label: 'FoodStore Online', href: route('tenant.foodstore.online', { tenant: props.tenant }), icon: Utensils, active: props.current === 'foodstore-online' },
            { label: 'Kitchen orders', href: route('tenant.foodstore.orders.index', { tenant: props.tenant }), icon: ClipboardList, active: props.current === 'kitchen-orders' },
        ],
    },
    {
        label: 'Catalogue',
        links: [
            { label: 'Products', href: route('tenant.products.index', { tenant: props.tenant }), icon: Package, active: props.current === 'products' },
            { label: 'Categories', href: route('tenant.categories.index', { tenant: props.tenant }), icon: Tags, active: props.current === 'categories' },
            { label: 'Food menu', href: route('tenant.foodstore.menu.index', { tenant: props.tenant }), icon: Utensils, active: props.current === 'food-menu' },
        ],
    },
    {
        label: 'Operations',
        links: [
            { label: 'Suppliers', href: route('tenant.suppliers.index', { tenant: props.tenant }), icon: Truck, active: props.current === 'suppliers' },
            { label: 'Team', href: route('tenant.team.index', { tenant: props.tenant }), icon: Users, active: props.current === 'team' },
            { label: 'Reports', href: route('tenant.reports', { tenant: props.tenant }), icon: FileText, active: props.current === 'reports' },
            ...(hasSalesExpensesAccess.value ? [{ label: 'Sales & expenses', href: route('tenant.sales-expenses.index', { tenant: props.tenant }), icon: Wallet, active: props.current === 'sales-expenses' }] : []),
            ...(hasAuditLogAccess.value ? [{ label: 'Audit log', href: route('tenant.audit', { tenant: props.tenant }), icon: ClipboardList, active: props.current === 'audit' }] : []),
        ],
    },
    {
        label: 'Insights & setup',
        links: [
            { label: 'Analytics', href: route('tenant.analytics', { tenant: props.tenant }), icon: BarChart3, active: props.current === 'analytics' },
            { label: 'Settings', href: route('tenant.settings.index', { tenant: props.tenant }), icon: Settings, active: props.current === 'settings' },
        ],
    },
]);
</script>

<template>
    <div v-if="!overlayMode && !mobileOpen" class="fixed top-4 right-4 z-30 flex items-center gap-2">
        <button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-md border border-neutral-200 bg-white px-4 text-sm font-semibold text-neutral-700 shadow-sm transition hover:border-neutral-300 hover:bg-neutral-50" title="Back to the previous page" @click="goBack">
            <ArrowLeft :size="17" aria-hidden="true" />
            <span>Back</span>
        </button>
        <form @submit.prevent="logout">
            <button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-md border border-neutral-200 bg-white px-4 text-sm font-semibold text-neutral-700 shadow-sm transition" :class="isFoodStoreTheme ? 'hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800' : 'hover:border-red-300 hover:bg-red-50 hover:text-red-700'" :disabled="logoutForm.processing">
                <LogOut :size="18" aria-hidden="true" />
                <span>Log out</span>
            </button>
        </form>
    </div>
    <div
        v-if="mobileOpen"
        class="fixed inset-0 z-10"
        :class="isFoodStoreTheme ? 'bg-emerald-950/45' : 'bg-[#171717]/40'"
        aria-hidden="true"
        @click="emit('close')"
    />
    <aside
        class="group/sidebar pointer-events-auto fixed inset-y-0 left-0 z-30 flex flex-col border-r text-white transition-transform duration-250"
        :class="[
            isFoodStoreTheme ? 'border-emerald-950/20 bg-emerald-900' : 'border-red-950/20 bg-[#8f1017]',
            mobileOpen ? 'translate-x-0' : '-translate-x-full',
            overlayMode ? 'h-dvh w-[18rem] max-w-[calc(100vw-1rem)] shadow-2xl' : 'w-64 lg:w-64',
        ]"
        aria-label="Admin center navigation"
    >
        <div class="flex min-h-24 items-center justify-between border-b border-white/15 px-5">
            <Link
                :href="route('tenant.products.index', { tenant })"
                class="flex items-center gap-3 font-bold tracking-tight"
                @click="emit('close')"
            >
                <span class="flex size-10 items-center justify-center rounded-xl bg-white" :class="isFoodStoreTheme ? 'text-emerald-800' : 'text-[#8f1017]'">
                    <ShoppingCart :size="22" aria-hidden="true" />
                </span>
                <span class="whitespace-nowrap opacity-100">Admin center</span>
            </Link>
            <button
                type="button"
                    class="rounded-md p-2 text-white/70 hover:bg-white/10 hover:text-white"
                :class="overlayMode ? '' : 'lg:hidden'"
                aria-label="Close navigation"
                @click="emit('close')"
            >
                <X :size="20" aria-hidden="true" />
            </button>
        </div>
        <div class="border-b border-white/15 px-5 py-5">
            <p class="whitespace-nowrap text-xs font-semibold tracking-wide uppercase opacity-100" :class="isFoodStoreTheme ? 'text-emerald-100/70' : 'text-red-100/70'">Subscriber workspace</p>
            <p class="mt-1 truncate font-semibold text-white opacity-100">{{ tenant }}</p>
        </div>
        <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-5">
            <div v-for="group in groups" :key="group.label" class="space-y-1">
                <p class="px-3 text-[10px] font-bold tracking-[0.18em] uppercase opacity-100" :class="isFoodStoreTheme ? 'text-emerald-100/55' : 'text-red-100/50'">{{ group.label }}</p>
                <Link v-for="link in group.links" :key="link.label" :href="link.href" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition" :class="link.active ? (isFoodStoreTheme ? 'bg-white text-emerald-900' : 'bg-white text-[#8f1017]') : (isFoodStoreTheme ? 'text-emerald-50/85 hover:bg-white/10 hover:text-white' : 'text-red-50/80 hover:bg-white/10 hover:text-white')" :title="link.label" @click="emit('close')">
                    <component :is="link.icon" class="size-5 shrink-0" aria-hidden="true" />
                    <span class="whitespace-nowrap opacity-100">{{ link.label }}</span>
                </Link>
            </div>
        </nav>
        <div class="border-t border-white/15 px-5 py-4 lg:px-3">
            <Link
                href="/dashboard"
                class="block truncate text-sm font-medium hover:text-white opacity-100"
                :class="isFoodStoreTheme ? 'text-emerald-100/75' : 'text-red-100/75'"
                @click="emit('close')"
            >
                Back to workspace launcher
            </Link>
            <button
                type="button"
                class="mt-4 flex w-full items-center gap-3 rounded-md border border-white/15 px-3 py-2.5 text-sm font-semibold transition hover:border-white/30 hover:bg-white/10 hover:text-white justify-start"
                :class="isFoodStoreTheme ? 'text-emerald-50/85' : 'text-red-50/85'"
                title="Log out of admin center"
                aria-label="Log out of admin center"
                @click="logout"
            >
                <LogOut class="size-5 shrink-0" aria-hidden="true" />
                <span class="whitespace-nowrap opacity-100">Log out</span>
            </button>
        </div>
    </aside>
</template>
