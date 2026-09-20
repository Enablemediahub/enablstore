<script setup lang="ts">
import { BarChart3, ClipboardList, FileText, LogOut, Package, Settings, ShoppingCart, Store, Tags, Truck, Users, X } from '@lucide/vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type PanelContext = 'storefront' | 'pos' | 'products' | 'categories' | 'settings' | 'analytics' | 'suppliers' | 'reports' | 'audit' | 'team';

const props = withDefaults(
    defineProps<{
        tenant: string;
        mobileOpen?: boolean;
        current?: PanelContext;
    }>(),
    {
        mobileOpen: false,
        current: 'products',
    },
);

const emit = defineEmits<{
    close: [];
}>();

const logoutForm = useForm({});
const logout = (): void => {
    logoutForm.post(route('logout'));
};

const groups = computed(() => [
    {
        label: 'Workspace',
        links: [
            { label: 'Storefront', href: route('tenant.home', { tenant: props.tenant }), icon: Store, active: props.current === 'storefront' },
            { label: 'Point of sale', href: route('tenant.pos', { tenant: props.tenant }), icon: ShoppingCart, active: props.current === 'pos' },
        ],
    },
    {
        label: 'Catalogue',
        links: [
            { label: 'Products', href: route('tenant.products.index', { tenant: props.tenant }), icon: Package, active: props.current === 'products' },
            { label: 'Categories', href: route('tenant.categories.index', { tenant: props.tenant }), icon: Tags, active: props.current === 'categories' },
        ],
    },
    {
        label: 'Operations',
        links: [
            { label: 'Suppliers', href: route('tenant.suppliers.index', { tenant: props.tenant }), icon: Truck, active: props.current === 'suppliers' },
            { label: 'Team', href: route('tenant.team.index', { tenant: props.tenant }), icon: Users, active: props.current === 'team' },
            { label: 'Reports', href: route('tenant.reports', { tenant: props.tenant }), icon: FileText, active: props.current === 'reports' },
            { label: 'Audit log', href: route('tenant.audit', { tenant: props.tenant }), icon: ClipboardList, active: props.current === 'audit' },
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
    <div
        v-if="mobileOpen"
        class="fixed inset-0 z-40 bg-[#171717]/40 lg:hidden"
        aria-hidden="true"
        @click="emit('close')"
    />
    <aside
        class="group/sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-red-950/20 bg-[#8f1017] text-white transition-[width,transform] duration-250 lg:static lg:z-auto lg:w-16 lg:translate-x-0 lg:hover:w-64"
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        aria-label="Admin center navigation"
    >
        <div class="flex min-h-24 items-center justify-between border-b border-white/15 px-5">
            <Link
                :href="route('tenant.products.index', { tenant })"
                class="flex items-center gap-3 font-bold tracking-tight"
                @click="emit('close')"
            >
                <span class="flex size-10 items-center justify-center rounded-xl bg-white text-[#8f1017]">
                    <ShoppingCart :size="22" aria-hidden="true" />
                </span>
                <span class="whitespace-nowrap lg:opacity-0 lg:transition-opacity lg:group-hover/sidebar:opacity-100">Admin center</span>
            </Link>
            <button
                type="button"
                class="rounded-md p-2 text-white/70 hover:bg-white/10 hover:text-white lg:hidden"
                aria-label="Close navigation"
                @click="emit('close')"
            >
                <X :size="20" aria-hidden="true" />
            </button>
        </div>
        <div class="border-b border-white/15 px-5 py-5">
            <p class="whitespace-nowrap text-xs font-semibold tracking-wide text-red-100/70 uppercase lg:opacity-0 lg:transition-opacity lg:group-hover/sidebar:opacity-100">Subscriber workspace</p>
            <p class="mt-1 truncate font-semibold text-white lg:opacity-0 lg:transition-opacity lg:group-hover/sidebar:opacity-100">{{ tenant }}</p>
        </div>
        <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-5">
            <div v-for="group in groups" :key="group.label" class="space-y-1">
                <p class="px-3 text-[10px] font-bold tracking-[0.18em] text-red-100/50 uppercase lg:opacity-0 lg:transition-opacity lg:group-hover/sidebar:opacity-100">{{ group.label }}</p>
                <Link v-for="link in group.links" :key="link.label" :href="link.href" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition" :class="link.active ? 'bg-white text-[#8f1017]' : 'text-red-50/80 hover:bg-white/10 hover:text-white'" :title="link.label" @click="emit('close')">
                    <component :is="link.icon" class="size-5 shrink-0" aria-hidden="true" />
                    <span class="whitespace-nowrap lg:opacity-0 lg:transition-opacity lg:group-hover/sidebar:opacity-100">{{ link.label }}</span>
                </Link>
            </div>
        </nav>
        <div class="border-t border-white/15 px-5 py-4 lg:px-3">
            <Link
                href="/dashboard"
                class="block truncate text-sm font-medium text-red-100/75 hover:text-white lg:opacity-0 lg:transition-opacity lg:group-hover/sidebar:opacity-100"
                @click="emit('close')"
            >
                Back to workspace launcher
            </Link>
            <button
                type="button"
                class="mt-4 flex w-full items-center gap-3 rounded-md border border-white/15 px-3 py-2.5 text-sm font-semibold text-red-50/85 transition hover:border-white/30 hover:bg-white/10 hover:text-white lg:justify-center lg:group-hover/sidebar:justify-start"
                title="Log out of admin center"
                aria-label="Log out of admin center"
                @click="logout"
            >
                <LogOut class="size-5 shrink-0" aria-hidden="true" />
                <span class="whitespace-nowrap lg:opacity-0 lg:transition-opacity lg:group-hover/sidebar:opacity-100">Log out</span>
            </button>
        </div>
    </aside>
</template>
