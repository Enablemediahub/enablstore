<script setup lang="ts">
import EnBadge from '@/Components/EnBadge.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Image, ShoppingCart, Store, ShieldCheck, Users } from '@lucide/vue';
import SuperAdminSidePanel from '@/Components/SuperAdminSidePanel.vue';

const props = defineProps<{
    metrics: {
        tenant_count: number;
        active_subscriptions: number;
        paid_revenue_minor: number;
    };
    tenants: {
        data: Array<{
            id: string;
            name: string;
            email: string;
            status: string;
        }>;
    };
    loginWallpaperUrl: string | null;
    status?: string | null;
    catalogueModeDefault: 'shared' | 'separate_online';
}>();

const brandingForm = useForm<{ login_wallpaper: File | null }>({
    login_wallpaper: null,
});
const catalogueForm = useForm({ catalogue_mode: props.catalogueModeDefault });

const uploadWallpaper = (): void => {
    brandingForm.post(route('super-admin.branding.login-wallpaper'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => brandingForm.reset('login_wallpaper'),
    });
};

const saveCatalogueMode = (): void => catalogueForm.patch(route('super-admin.catalogue-mode.update'));

const formatGhs = (minor: number): string =>
    new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(minor / 100);
</script>

<template>
    <Head title="Super admin dashboard" />
    <main class="flex min-h-screen bg-neutral-50">
        <SuperAdminSidePanel current="dashboard" />
        <div class="min-w-0 flex-1 px-4 py-10 sm:px-8">
            <div class="mx-auto max-w-7xl space-y-8">
            <header>
                <p class="text-primary-700 text-sm font-semibold">
                    Control panel
                </p>
                <h1 class="mt-1 text-3xl font-bold text-neutral-900">
                    Platform overview
                </h1>
            </header>
            <section id="control-center" class="border border-neutral-200 bg-white p-6 shadow-sm sm:p-7">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold tracking-[0.18em] text-[#e21b23] uppercase">Control center</p>
                        <h2 class="mt-1 text-xl font-black text-neutral-900">Manage platform access</h2>
                    </div>
                    <p class="text-sm text-neutral-500">Choose a control area below.</p>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <a href="#branding" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><Image :size="19" aria-hidden="true" /><span>Branding settings</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></a>
                    <Link :href="route('super-admin.users.index')" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><Store :size="19" aria-hidden="true" /><span>Online Store access</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                    <Link :href="route('super-admin.users.index')" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><ShoppingCart :size="19" aria-hidden="true" /><span>POS access</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                    <Link :href="route('super-admin.users.index')" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><Users :size="19" aria-hidden="true" /><span>Manage users</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                    <Link :href="route('super-admin.accounts.index')" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><ShieldCheck :size="19" aria-hidden="true" /><span>Manage admins</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                </div>
            </section>
            <EnCard id="branding">
                <div class="grid gap-6 lg:grid-cols-[1fr_280px] lg:items-center">
                    <div>
                        <p class="text-primary-700 text-sm font-semibold">Branding</p>
                        <h2 class="mt-1 text-xl font-bold text-neutral-900">Login wallpaper</h2>
                        <p class="mt-2 max-w-xl text-sm text-neutral-500">Upload the background image customers see behind the Enablstore login screen. JPG, PNG, or WebP up to 5 MB.</p>
                        <form class="mt-5 flex flex-wrap items-end gap-3" @submit.prevent="uploadWallpaper">
                            <label class="block text-sm font-medium text-neutral-700">Choose image<input type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-neutral-600" @change="brandingForm.login_wallpaper = ($event.target as HTMLInputElement).files?.[0] ?? null" /></label>
                            <button type="submit" class="min-h-10 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60" :disabled="!brandingForm.login_wallpaper || brandingForm.processing">Save login wallpaper</button>
                        </form>
                        <p v-if="brandingForm.errors.login_wallpaper" class="mt-2 text-sm text-red-600">{{ brandingForm.errors.login_wallpaper }}</p>
                        <p v-if="status" class="mt-2 text-sm text-green-600">{{ status }}</p>
                    </div>
                    <div class="aspect-video overflow-hidden rounded-md bg-neutral-100">
                        <img v-if="loginWallpaperUrl" :src="loginWallpaperUrl" alt="Current login wallpaper" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full items-center justify-center px-5 text-center text-sm text-neutral-500">No custom wallpaper uploaded yet.</div>
                    </div>
                </div>
            </EnCard>
            <EnCard>
                <p class="text-primary-700 text-sm font-semibold">Catalogue defaults</p>
                <h2 class="mt-1 text-xl font-bold text-neutral-900">Default Online Store mode</h2>
                <p class="mt-2 text-sm text-neutral-500">This is the default for tenants that have not chosen their own Admin center setting.</p>
                <form class="mt-5 flex flex-wrap items-center gap-4" @submit.prevent="saveCatalogueMode">
                    <label class="text-sm"><input v-model="catalogueForm.catalogue_mode" type="radio" value="shared" class="mr-2" /> Shared products</label>
                    <label class="text-sm"><input v-model="catalogueForm.catalogue_mode" type="radio" value="separate_online" class="mr-2" /> Separate Online Store products</label>
                    <button type="submit" class="rounded-md bg-[#e21b23] px-4 py-2 text-sm font-semibold text-white" :disabled="catalogueForm.processing">Save default</button>
                </form>
            </EnCard>
            <div class="grid gap-5 md:grid-cols-3">
                <EnCard
                    ><p class="text-sm text-neutral-500">Tenants</p>
                    <p class="mt-2 text-3xl font-bold text-neutral-900">
                        {{ metrics.tenant_count }}
                    </p></EnCard
                >
                <EnCard
                    ><p class="text-sm text-neutral-500">
                        Active subscriptions
                    </p>
                    <p class="mt-2 text-3xl font-bold text-neutral-900">
                        {{ metrics.active_subscriptions }}
                    </p></EnCard
                >
                <EnCard
                    ><p class="text-sm text-neutral-500">Paid revenue</p>
                    <p
                        class="mt-2 font-mono text-3xl font-bold text-neutral-900"
                    >
                        {{ formatGhs(metrics.paid_revenue_minor) }}
                    </p></EnCard
                >
            </div>
            <EnCard>
                <h2 class="text-lg font-semibold text-neutral-900">Tenants</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-neutral-100 text-xs text-neutral-500 uppercase"
                        >
                            <tr>
                                <th class="px-3 py-3">Store</th>
                                <th class="px-3 py-3">Email</th>
                                <th class="px-3 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="tenant in tenants.data"
                                :key="tenant.id"
                                class="border-b border-neutral-100 last:border-0"
                            >
                                <td class="px-3 py-4 font-semibold">
                                    {{ tenant.name }}
                                </td>
                                <td class="px-3 py-4">{{ tenant.email }}</td>
                                <td class="px-3 py-4">
                                    <EnBadge>{{ tenant.status }}</EnBadge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </EnCard>
            </div>
        </div>
    </main>
</template>
