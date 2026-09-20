<!--
Props:
- tenant: current tenant path key
- mobileOpen: whether the mobile drawer is visible
- current: storefront or pos context

Emits:
- close: emitted when the mobile drawer should close

Slots:
- none
-->
<script setup lang="ts">
import { BarChart3, Package, ShoppingCart, Store, X } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type PanelContext = 'storefront' | 'pos';

const props = withDefaults(
    defineProps<{
        tenant: string;
        mobileOpen?: boolean;
        current?: PanelContext;
    }>(),
    {
        mobileOpen: false,
        current: 'storefront',
    },
);

const emit = defineEmits<{
    close: [];
}>();

const links = computed(() => [
    {
        label: 'Storefront',
        href: route('tenant.home', { tenant: props.tenant }),
        icon: Store,
        active: props.current === 'storefront',
    },
    {
        label: 'Point of sale',
        href: route('tenant.pos', { tenant: props.tenant }),
        icon: ShoppingCart,
        active: props.current === 'pos',
    },
    {
        label: 'Products',
        href: route('tenant.products.index', { tenant: props.tenant }),
        icon: Package,
        active: false,
    },
    {
        label: 'Analytics',
        href: route('tenant.analytics', { tenant: props.tenant }),
        icon: BarChart3,
        active: false,
    },
]);
</script>

<template>
    <div
        v-if="mobileOpen"
        class="fixed inset-0 z-40 bg-neutral-900/30 lg:hidden"
        aria-hidden="true"
        @click="emit('close')"
    />
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-neutral-200 bg-white transition-transform duration-250 lg:static lg:z-auto lg:translate-x-0"
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        aria-label="Store navigation"
    >
        <div
            class="flex min-h-24 items-center justify-between border-b border-neutral-100 px-5"
        >
            <Link
                :href="route('tenant.home', { tenant })"
                class="flex items-center gap-2 font-bold tracking-tight"
                @click="emit('close')"
            >
                <img
                    src="/images/Enablstore-cropped.png"
                    alt="Enablstore"
                    class="h-20 w-auto max-w-[64px] object-contain"
                />
            </Link>
            <button
                type="button"
                class="rounded-md p-2 text-neutral-500 hover:bg-neutral-100 lg:hidden"
                aria-label="Close navigation"
                @click="emit('close')"
            >
                <X :size="20" aria-hidden="true" />
            </button>
        </div>
        <div class="border-b border-neutral-100 px-5 py-5">
            <p
                class="text-xs font-semibold tracking-wide text-neutral-500 uppercase"
            >
                Workspace
            </p>
            <p class="mt-1 truncate font-semibold text-neutral-900">
                {{ tenant }}
            </p>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-5">
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.href"
                class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition"
                :class="
                    link.active
                        ? 'bg-primary-50 text-primary-700'
                        : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900'
                "
                @click="emit('close')"
            >
                <component :is="link.icon" :size="20" aria-hidden="true" />
                {{ link.label }}
            </Link>
        </nav>
        <div class="border-t border-neutral-100 px-5 py-4">
            <Link
                href="/"
                class="text-sm font-medium text-neutral-500 hover:text-neutral-900"
                @click="emit('close')"
            >
                Back to Enablstore
            </Link>
        </div>
    </aside>
</template>
