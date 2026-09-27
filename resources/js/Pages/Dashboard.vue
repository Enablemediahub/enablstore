<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowUpRight, Check, LayoutDashboard, LogOut, ShoppingCart, Soup, Store, Utensils, X } from '@lucide/vue';
import { ref } from 'vue';

defineProps<{
    user: { name: string; username: string; role: 'admin' | 'cashier' } | null;
    tenant: { id: string; name: string; slug: string } | null;
    subscription: { plan: string | null; status: string } | null;
    access: { onlineStore: boolean; pos: boolean; restaurantFoodStore: boolean; foodstoreOnline: boolean };
    wallpaperUrl: string | null;
    adminDashboard?: boolean;
}>();

const logoutForm = useForm({});
const adminCenterOpen = ref(false);

const logout = (): void => {
    logoutForm.post(route('logout'));
};
</script>

<template>
    <Head title="Choose a workspace" />
    <main class="relative min-h-screen overflow-hidden bg-[#f6f3ef] text-[#171717]">
        <div v-if="wallpaperUrl" class="pointer-events-none absolute inset-0 bg-cover bg-center bg-no-repeat" :style="{ backgroundImage: `url('${wallpaperUrl}')` }" aria-hidden="true" />
        <div v-if="wallpaperUrl" class="pointer-events-none absolute inset-0 bg-[#171717]/60 backdrop-blur-[1px]" aria-hidden="true" />
        <div class="relative z-10 flex min-h-screen">
        <AdminSidePanel v-if="user?.role === 'admin' && tenant && adminCenterOpen" id="admin-center-sidebar" :tenant="tenant.slug" current="workspace" :mobile-open="adminCenterOpen" :overlay-mode="true" @close="adminCenterOpen = false" />
        <div class="min-w-0 flex-1">
        <header class="relative z-40 border-b border-black/5 bg-[#171717]/95 px-5 py-4 text-white backdrop-blur sm:px-10">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
                <Link href="/" class="inline-flex items-center gap-3 text-xl font-black tracking-tight">
                    <span class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-[#e21b23] bg-white p-1 shadow-sm ring-2 ring-white/15">
                        <img src="/images/Enablstore-cropped.png" alt="Enablstore logo" class="h-full w-full rounded-full object-contain" />
                    </span>
                    <span>enabl<span class="text-[#e21b23]">store</span></span>
                </Link>
                <div class="flex items-center gap-2 text-sm sm:gap-4">
                    <span v-if="user" class="hidden text-white/65 sm:inline">{{ user.name }}</span>
                    <Link v-if="!user" href="/login" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700">Admin login</Link>
                    <template v-if="user && tenant && user.role === 'admin'">
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-white/15 px-3 py-2 text-white/75 transition hover:border-white/40 hover:text-white sm:px-4" :aria-expanded="adminCenterOpen" aria-controls="admin-center-sidebar" @click="adminCenterOpen = !adminCenterOpen"><component :is="adminCenterOpen ? X : LayoutDashboard" :size="16" aria-hidden="true" /><span class="hidden sm:inline">{{ adminCenterOpen ? 'Close admin center' : 'Admin center' }}</span><span class="sm:hidden">Admin</span></button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-white/15 px-3 py-2 text-white/75 transition hover:border-white/40 hover:text-white sm:px-4" :disabled="logoutForm.processing" @click="logout"><LogOut :size="16" aria-hidden="true" /><span class="hidden sm:inline">Sign out</span></button>
                    </template>
                    <button v-else-if="user" type="button" class="inline-flex items-center gap-2 rounded-full border border-white/15 px-3 py-2 text-white/75 transition hover:border-white/40 hover:text-white sm:px-4" @click="logout"><LogOut :size="16" aria-hidden="true" /><span class="hidden sm:inline">Sign out</span></button>
                </div>
            </div>
        </header>
        <section class="relative mx-auto max-w-7xl px-5 py-10 sm:px-10 sm:py-16">
            <div class="pointer-events-none absolute -top-20 right-0 size-80 rounded-full bg-[#e21b23]/8 blur-3xl" aria-hidden="true" />
            <div v-if="user" class="relative flex flex-wrap items-end justify-between gap-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-black tracking-[0.24em] uppercase" :class="wallpaperUrl ? 'text-[#ff8b8f]' : 'text-[#e21b23]'">Workspace launcher</p>
                    <h1 class="mt-4 text-4xl font-black tracking-[-0.03em] sm:text-6xl" :class="wallpaperUrl ? 'text-white' : 'text-[#171717]'">{{ adminDashboard ? 'Admin dashboard' : 'Choose a workspace' }}</h1>
                    <p class="mt-5 max-w-xl text-base leading-7" :class="wallpaperUrl ? 'text-white/80' : 'text-[#555555]'">{{ adminDashboard ? `Manage ${tenant?.name ?? 'your store'} and open its business workspaces.` : `Choose the workspace you need for ${tenant?.name ?? 'your store'}.` }}</p>
                </div>
                <div v-if="subscription" class="rounded-2xl border border-black/5 bg-white/80 px-5 py-4 shadow-sm backdrop-blur"><p class="text-xs font-bold tracking-widest text-[#777777] uppercase">Current plan</p><div class="mt-1 flex items-center gap-2"><strong class="text-lg">{{ subscription.plan }}</strong><span class="size-1.5 rounded-full bg-[#e21b23]" aria-hidden="true" /><span class="text-sm capitalize text-[#555555]">{{ subscription.status }}</span></div></div>
            </div>
            <div v-else class="relative max-w-3xl py-8 sm:py-12">
                <p class="text-xs font-black tracking-[0.24em] uppercase" :class="wallpaperUrl ? 'text-[#ff8b8f]' : 'text-[#e21b23]'">Workspace launcher</p>
                <h1 class="mt-4 text-4xl font-black tracking-[-0.03em] sm:text-6xl" :class="wallpaperUrl ? 'text-white' : 'text-[#171717]'">Choose a workspace</h1>
            </div>
            <div v-if="tenant" class="relative mt-12 grid gap-6 lg:grid-cols-2">
                <Link v-if="access.onlineStore" :href="route('tenant.home', { tenant: tenant.slug })" class="group relative min-h-[350px] overflow-hidden rounded-[2rem] border border-[#e21b23]/15 bg-white p-7 shadow-[0_20px_60px_rgba(23,23,23,0.08)] transition duration-300 hover:-translate-y-2 hover:shadow-[0_28px_70px_rgba(226,27,35,0.18)] sm:p-10">
                    <img src="/images/products/catalogue.svg" alt="" class="pointer-events-none absolute -right-8 -bottom-12 w-64 opacity-[0.13] mix-blend-multiply transition duration-500 group-hover:scale-110 group-hover:rotate-3 group-hover:opacity-[0.2]" aria-hidden="true" />
                    <div class="relative flex items-start justify-between"><div class="flex size-16 -translate-y-2 items-center justify-center rounded-[1.35rem] bg-[#e21b23] text-white shadow-[0_14px_28px_rgba(226,27,35,0.3)] transition duration-300 group-hover:-translate-y-4 group-hover:rotate-3"><Store :size="30" stroke-width="2.2" aria-hidden="true" /></div><span class="flex size-11 items-center justify-center rounded-full border border-neutral-200 bg-white text-[#171717] transition group-hover:border-[#e21b23] group-hover:bg-[#e21b23] group-hover:text-white"><ArrowUpRight :size="20" aria-hidden="true" /></span></div>
                    <div class="relative mt-14 max-w-sm"><p class="text-xs font-black tracking-[0.2em] text-[#e21b23] uppercase">Customer experience</p><h2 class="mt-3 text-3xl font-black tracking-tight">Online Store</h2><p class="mt-3 text-sm leading-6 text-[#555555]">Manage your customer-facing catalogue and shopping experience.</p><span class="mt-7 inline-flex items-center gap-2 rounded-full bg-[#e21b23] px-5 py-3 text-sm font-bold text-white">Open store <ArrowUpRight :size="16" aria-hidden="true" /></span></div>
                </Link>
                <Link v-if="access.pos" :href="route('tenant.pos', { tenant: tenant.slug })" class="group relative min-h-[350px] overflow-hidden rounded-[2rem] border border-black/10 bg-[#202020] p-7 text-white shadow-[0_20px_60px_rgba(23,23,23,0.15)] transition duration-300 hover:-translate-y-2 hover:shadow-[0_28px_70px_rgba(23,23,23,0.25)] sm:p-10">
                    <img src="/images/products/cart.svg" alt="" class="pointer-events-none absolute -right-10 -bottom-12 w-64 opacity-[0.13] brightness-0 invert transition duration-500 group-hover:scale-110 group-hover:-rotate-3 group-hover:opacity-[0.2]" aria-hidden="true" />
                    <div class="relative flex items-start justify-between"><div class="flex size-16 -translate-y-2 items-center justify-center rounded-[1.35rem] bg-white text-[#171717] shadow-[0_14px_28px_rgba(0,0,0,0.25)] transition duration-300 group-hover:-translate-y-4 group-hover:-rotate-3"><ShoppingCart :size="30" stroke-width="2.2" aria-hidden="true" /></div><span class="flex size-11 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition group-hover:border-[#ff6d72] group-hover:bg-[#e21b23]"><ArrowUpRight :size="20" aria-hidden="true" /></span></div>
                    <div class="relative mt-14 max-w-sm"><p class="text-xs font-black tracking-[0.2em] text-[#ff8b8f] uppercase">Daily operations</p><h2 class="mt-3 text-3xl font-black tracking-tight">Point of Sale</h2><p class="mt-3 text-sm leading-6 text-white/70">Ring up counter sales, choose payment methods, and keep selling offline.</p><span class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-[#171717]">Open POS <ArrowUpRight :size="16" aria-hidden="true" /></span></div>
                </Link>
                <Link v-if="access.restaurantFoodStore" :href="route('tenant.foodstore.index', { tenant: tenant.slug })" class="group relative min-h-[350px] overflow-hidden rounded-[2rem] border border-[#176b50]/20 bg-[#176b50] p-7 text-white shadow-[0_20px_60px_rgba(23,23,23,0.12)] transition duration-300 hover:-translate-y-2 hover:shadow-[0_28px_70px_rgba(23,23,23,0.2)] sm:p-10">
                    <Soup :size="190" :stroke-width="1.1" class="pointer-events-none absolute right-7 -bottom-8 -rotate-12 text-white/10 opacity-100 transition duration-500 group-hover:scale-110 group-hover:rotate-0 group-hover:text-white/20" aria-hidden="true" />
                    <div class="relative flex items-start justify-between"><div class="flex size-16 -translate-y-2 items-center justify-center rounded-[1.35rem] bg-white text-[#176b50] shadow-[0_14px_28px_rgba(0,0,0,0.25)] transition duration-300 group-hover:-translate-y-4 group-hover:rotate-3"><Utensils :size="30" stroke-width="2.2" aria-hidden="true" /></div><span class="flex size-11 items-center justify-center rounded-full border border-white/25 bg-white/10 text-white transition group-hover:bg-white group-hover:text-[#176b50]"><ArrowUpRight :size="20" aria-hidden="true" /></span></div>
                    <div class="relative mt-14 max-w-sm"><p class="text-xs font-black tracking-[0.2em] text-emerald-100 uppercase">Restaurant operations</p><h2 class="mt-3 text-3xl font-black tracking-tight">FoodStore POS</h2><p class="mt-3 text-sm leading-6 text-white/80">Sell plates, meal packs, and grilled food at the restaurant.</p><span class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-[#176b50]">Open FoodStore POS <ArrowUpRight :size="16" aria-hidden="true" /></span></div>
                </Link>
                <Link v-if="access.foodstoreOnline" :href="route('tenant.foodstore.online', { tenant: tenant.slug })" class="group relative min-h-[350px] overflow-hidden rounded-[2rem] border border-emerald-800/15 bg-[#e8f3ec] p-7 text-[#153c2d] shadow-[0_20px_60px_rgba(23,23,23,0.08)] transition duration-300 hover:-translate-y-2 hover:shadow-[0_28px_70px_rgba(23,23,23,0.16)] sm:p-10">
                    <Utensils :size="190" :stroke-width="1.1" class="pointer-events-none absolute right-7 -bottom-8 -rotate-12 text-emerald-900/10 transition duration-500 group-hover:scale-110 group-hover:rotate-0 group-hover:text-emerald-900/20" aria-hidden="true" />
                    <div class="relative flex items-start justify-between"><div class="flex size-16 -translate-y-2 items-center justify-center rounded-[1.35rem] bg-emerald-800 text-white shadow-[0_14px_28px_rgba(0,0,0,0.18)] transition duration-300 group-hover:-translate-y-4 group-hover:rotate-3"><Soup :size="30" stroke-width="2.2" aria-hidden="true" /></div><span class="flex size-11 items-center justify-center rounded-full border border-emerald-900/15 bg-white/80 text-emerald-900 transition group-hover:bg-emerald-800 group-hover:text-white"><ArrowUpRight :size="20" aria-hidden="true" /></span></div>
                    <div class="relative mt-14 max-w-sm"><p class="text-xs font-black tracking-[0.2em] text-emerald-800 uppercase">Customer ordering</p><h2 class="mt-3 text-3xl font-black tracking-tight">FoodStore Online</h2><p class="mt-3 text-sm leading-6 text-emerald-950/70">Let customers browse your food menu and send orders online.</p><span class="mt-7 inline-flex items-center gap-2 rounded-full bg-emerald-800 px-5 py-3 text-sm font-bold text-white">Open online menu <ArrowUpRight :size="16" aria-hidden="true" /></span></div>
                </Link>
                <div v-if="!access.pos" class="relative min-h-[350px] overflow-hidden rounded-[2rem] border border-dashed border-neutral-300 bg-[#e9e5df] p-7 opacity-75 sm:p-10"><ShoppingCart class="text-neutral-500" :size="34" aria-hidden="true" /><h2 class="mt-12 text-3xl font-black">Point of Sale</h2><p class="mt-3 max-w-sm text-sm leading-6 text-[#555555]">POS access is managed by your superadmin and is not included in the current plan.</p><span class="mt-7 inline-flex rounded-full bg-neutral-400 px-5 py-3 text-sm font-bold text-white">Access not granted</span></div>
                <div v-if="!access.restaurantFoodStore" class="relative min-h-[350px] overflow-hidden rounded-[2rem] border border-dashed border-neutral-300 bg-[#e9e5df] p-7 opacity-75 sm:p-10"><Utensils class="text-neutral-500" :size="34" aria-hidden="true" /><h2 class="mt-12 text-3xl font-black">FoodStore POS</h2><p class="mt-3 max-w-sm text-sm leading-6 text-[#555555]">Access is managed by your superadmin and is not included in your subscription.</p><span class="mt-7 inline-flex rounded-full bg-neutral-400 px-5 py-3 text-sm font-bold text-white">Access not granted</span></div>
                <div v-if="!access.foodstoreOnline" class="relative min-h-[350px] overflow-hidden rounded-[2rem] border border-dashed border-neutral-300 bg-[#e9e5df] p-7 opacity-75 sm:p-10"><Soup class="text-neutral-500" :size="34" aria-hidden="true" /><h2 class="mt-12 text-3xl font-black">FoodStore Online</h2><p class="mt-3 max-w-sm text-sm leading-6 text-[#555555]">Customer ordering is managed by your superadmin and is not included in your subscription.</p><span class="mt-7 inline-flex rounded-full bg-neutral-400 px-5 py-3 text-sm font-bold text-white">Access not granted</span></div>
                <div v-if="!access.onlineStore" class="relative min-h-[350px] overflow-hidden rounded-[2rem] border border-dashed border-neutral-300 bg-[#e9e5df] p-7 opacity-75 sm:p-10"><Store class="text-neutral-500" :size="34" aria-hidden="true" /><h2 class="mt-12 text-3xl font-black">Online Store</h2><p class="mt-3 max-w-sm text-sm leading-6 text-[#555555]">Online Store access is managed by your superadmin and is not included in the current plan.</p><span class="mt-7 inline-flex rounded-full bg-neutral-400 px-5 py-3 text-sm font-bold text-white">Access not granted</span></div>
            </div>
            <div v-else-if="user" class="relative mt-10 rounded-[2rem] border border-red-200 bg-red-50 p-7 text-sm text-red-800">No workspace is assigned to this account yet. Ask your superadmin to assign a store.</div>
            <div v-if="user && tenant && !access.onlineStore && !access.pos && !access.restaurantFoodStore && !access.foodstoreOnline" class="relative mt-6 flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800"><Check :size="18" aria-hidden="true" /> Your current subscription does not include an Online Store, POS, or FoodStore workspace. Ask your superadmin to update your plan.</div>
        </section>
        </div>
        </div>
    </main>
</template>
