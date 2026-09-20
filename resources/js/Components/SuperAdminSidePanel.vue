<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Image, LayoutDashboard, ShieldCheck, ShoppingCart, Store, Users } from '@lucide/vue';

defineProps<{ current: 'dashboard' | 'users' | 'accounts' }>();

const links = [
    { key: 'dashboard', label: 'Overview', href: 'super-admin.dashboard', icon: LayoutDashboard },
    { key: 'branding', label: 'Branding settings', href: 'super-admin.dashboard', icon: Image },
    { key: 'store-access', label: 'Online Store access', href: 'super-admin.users.index', icon: Store },
    { key: 'pos-access', label: 'POS access', href: 'super-admin.users.index', icon: ShoppingCart },
    { key: 'users', label: 'Workspace users', href: 'super-admin.users.index', icon: Users },
    { key: 'accounts', label: 'Superadmins', href: 'super-admin.accounts.index', icon: ShieldCheck },
];
</script>

<template>
    <aside class="hidden min-h-screen w-64 shrink-0 flex-col bg-[#171717] text-white lg:flex">
        <div class="border-b border-white/10 px-6 py-6">
            <div class="flex size-20 items-center justify-center overflow-hidden rounded-full border-2 border-[#e21b23] bg-white p-2 ring-4 ring-white/10">
                <img src="/images/Enablstore-cropped.png" alt="Enablstore" class="h-full w-full object-contain" />
            </div>
            <p class="mt-3 text-xs font-bold tracking-[0.18em] text-[#ff8b8f] uppercase">Superadmin</p>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-6">
            <Link v-for="link in links" :key="link.key" :href="link.key === 'branding' ? `${route(link.href)}#branding` : route(link.href)" class="flex items-center gap-3 rounded-md px-3 py-3 text-sm font-semibold transition" :class="current === link.key ? 'bg-[#e21b23] text-white' : 'text-neutral-300 hover:bg-white/10 hover:text-white'">
                <component :is="link.icon" :size="19" aria-hidden="true" />{{ link.label }}
            </Link>
        </nav>
        <div class="border-t border-white/10 px-6 py-5"><Link href="/" class="text-sm text-neutral-400 hover:text-white">Back to login</Link></div>
    </aside>
</template>
