<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, Menu, Settings, ShieldCheck, X } from '@lucide/vue';
import { ref } from 'vue';

defineProps<{ current: 'dashboard' | 'tenants' | 'accounts' }>();

const mobileOpen = ref(false);

const links = [
    { key: 'dashboard', label: 'Overview & branding', href: 'super-admin.dashboard', icon: LayoutDashboard },
    { key: 'accounts', label: 'Superadmins', href: 'super-admin.accounts.index', icon: ShieldCheck },
];
</script>

<template>
    <button type="button" class="fixed top-4 right-4 z-30 inline-flex size-11 items-center justify-center rounded-md border border-neutral-200 bg-white text-[#171717] shadow-sm lg:hidden" :aria-expanded="mobileOpen" aria-controls="superadmin-navigation" aria-label="Open superadmin navigation" @click="mobileOpen = true">
        <Menu :size="21" aria-hidden="true" />
    </button>
    <div v-if="mobileOpen" class="fixed inset-0 z-40 bg-neutral-900/50 lg:hidden" aria-hidden="true" @click="mobileOpen = false" />
    <aside id="superadmin-navigation" class="fixed inset-y-0 left-0 z-50 flex min-h-screen w-64 shrink-0 flex-col bg-[#171717] text-white transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0" :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'" aria-label="Superadmin navigation">
        <div class="border-b border-white/10 px-6 py-6">
            <div class="flex items-start justify-between gap-4"><div><div class="flex size-20 items-center justify-center overflow-hidden rounded-full border-2 border-[#e21b23] bg-white p-2 ring-4 ring-white/10"><img src="/images/Enablstore-cropped.png" alt="Enablstore" class="h-full w-full object-contain" /></div><p class="mt-3 text-xs font-bold tracking-[0.18em] text-[#ff8b8f] uppercase">Superadmin</p></div><button type="button" class="rounded-md p-2 text-neutral-300 hover:bg-white/10 hover:text-white lg:hidden" aria-label="Close superadmin navigation" @click="mobileOpen = false"><X :size="20" aria-hidden="true" /></button></div>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-6">
            <Link :href="route('super-admin.tenants.index')" class="flex items-center gap-3 rounded-md px-3 py-3 text-sm font-semibold transition" :class="current === 'tenants' ? 'bg-[#e21b23] text-white' : 'text-neutral-300 hover:bg-white/10 hover:text-white'" @click="mobileOpen = false">
                <Settings :size="19" aria-hidden="true" /> Subscribers
            </Link>
            <Link v-for="link in links" :key="link.key" :href="route(link.href)" class="flex items-center gap-3 rounded-md px-3 py-3 text-sm font-semibold transition" :class="current === link.key ? 'bg-[#e21b23] text-white' : 'text-neutral-300 hover:bg-white/10 hover:text-white'" @click="mobileOpen = false">
                <component :is="link.icon" :size="19" aria-hidden="true" />{{ link.label }}
            </Link>
        </nav>
        <div class="border-t border-white/10 px-6 py-5"><Link href="/" class="text-sm text-neutral-400 hover:text-white">Back to login</Link></div>
    </aside>
</template>
