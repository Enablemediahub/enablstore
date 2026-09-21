<script setup lang="ts">
import EnBadge from '@/Components/EnBadge.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Image, Settings, ShoppingCart, Store, ShieldCheck, Trash2, Users } from '@lucide/vue';
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
            subscription_plan: string | null;
            subscription_status: string | null;
        }>;
    };
    dashboardWallpaperUrl: string | null;
    loginWallpaperUrl: string | null;
    storefrontLogoUrl: string;
    defaultStorefrontLogoUrl: string;
    hasCustomStorefrontLogo: boolean;
    storefrontTenantDisplay: {
        enabled: boolean;
        placement: 'header' | 'hero' | 'both';
        label_prefix: string;
    };
    status?: string | null;
    catalogueModeDefault: 'shared' | 'separate_online';
}>();

const brandingForm = useForm<{ login_wallpaper: File | null }>({
    login_wallpaper: null,
});
const dashboardWallpaperForm = useForm<{ dashboard_wallpaper: File | null }>({
    dashboard_wallpaper: null,
});
const logoForm = useForm<{ storefront_logo: File | null }>({
    storefront_logo: null,
});
const tenantDisplayForm = useForm({
    enabled: props.storefrontTenantDisplay.enabled,
    placement: props.storefrontTenantDisplay.placement,
    label_prefix: props.storefrontTenantDisplay.label_prefix,
});
const catalogueForm = useForm({ catalogue_mode: props.catalogueModeDefault });

const previewTenantName = props.tenants.data[0]?.name ?? 'Demo Store';

const uploadWallpaper = (): void => {
    brandingForm.post(route('super-admin.branding.login-wallpaper'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => brandingForm.reset('login_wallpaper'),
    });
};

const uploadDashboardWallpaper = (): void => {
    dashboardWallpaperForm.post(route('super-admin.branding.dashboard-wallpaper'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => dashboardWallpaperForm.reset('dashboard_wallpaper'),
    });
};

const uploadStorefrontLogo = (): void => {
    logoForm.post(route('super-admin.branding.storefront-logo'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => logoForm.reset('storefront_logo'),
    });
};

const resetStorefrontLogo = (): void => {
    logoForm.delete(route('super-admin.branding.storefront-logo.destroy'), {
        preserveScroll: true,
    });
};

const saveCatalogueMode = (): void => catalogueForm.patch(route('super-admin.catalogue-mode.update'));

const saveTenantDisplay = (): void => {
    tenantDisplayForm.patch(route('super-admin.branding.storefront-tenant-display'), {
        preserveScroll: true,
    });
};

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
                    <Link :href="route('super-admin.tenants.index', { feature: 'online_store' })" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><Store :size="19" aria-hidden="true" /><span>Online Store access</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                    <Link :href="route('super-admin.tenants.index', { feature: 'pos' })" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><ShoppingCart :size="19" aria-hidden="true" /><span>POS access</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                    <Link :href="route('super-admin.tenants.index')" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><Users :size="19" aria-hidden="true" /><span>Subscribers & team</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                    <Link :href="route('super-admin.accounts.index')" class="group flex items-center gap-3 border border-neutral-200 px-4 py-3 text-left text-sm font-bold hover:border-[#e21b23] hover:text-[#e21b23]"><ShieldCheck :size="19" aria-hidden="true" /><span>Manage admins</span><ArrowRight :size="16" class="ml-auto transition group-hover:translate-x-1" aria-hidden="true" /></Link>
                </div>
            </section>
            <EnCard id="tenants">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><p class="text-primary-700 text-sm font-semibold">Workspace administration</p><h2 class="mt-1 text-lg font-semibold text-neutral-900">Enrolled tenants</h2></div>
                    <Link :href="route('super-admin.tenants.index')" class="text-sm font-semibold text-[#e21b23] hover:text-[#b9151b]">Open tenant directory</Link>
                </div>
                <div class="mt-5 overflow-x-auto"><table class="w-full text-left text-sm"><thead class="border-b border-neutral-100 text-xs text-neutral-500 uppercase"><tr><th class="px-3 py-3">Store</th><th class="px-3 py-3">Email</th><th class="px-3 py-3">Subscription</th><th class="px-3 py-3">Status</th><th class="px-3 py-3"><span class="sr-only">Manage</span></th></tr></thead><tbody><tr v-for="tenant in tenants.data" :key="tenant.id" class="border-b border-neutral-100 last:border-0"><td class="px-3 py-4 font-semibold">{{ tenant.name }}</td><td class="px-3 py-4">{{ tenant.email }}</td><td class="px-3 py-4"><p class="font-medium text-neutral-800">{{ tenant.subscription_plan ?? 'Not enrolled' }}</p><p v-if="tenant.subscription_status" class="mt-0.5 text-xs text-neutral-500">{{ tenant.subscription_status }}</p></td><td class="px-3 py-4"><EnBadge>{{ tenant.status }}</EnBadge></td><td class="px-3 py-4 text-right"><Link :href="route('super-admin.tenants.show', { tenant: tenant.id })" class="inline-flex items-center gap-2 font-semibold text-[#e21b23] hover:text-[#b9151b]"><Settings :size="16" aria-hidden="true" /> Manage</Link></td></tr></tbody></table></div>
            </EnCard>
            <EnCard id="branding">
                <div class="space-y-10">
                    <div class="grid gap-6 lg:grid-cols-[1fr_280px] lg:items-center">
                        <div>
                            <p class="text-primary-700 text-sm font-semibold">Branding</p>
                            <h2 class="mt-1 text-xl font-bold text-neutral-900">Storefront logo</h2>
                            <p class="mt-2 max-w-xl text-sm text-neutral-500">
                                Upload the logo shown in the header of every tenant online store. PNG, JPG, WebP, or SVG up to 5 MB.
                            </p>
                            <form class="mt-5 flex flex-wrap items-end gap-3" @submit.prevent="uploadStorefrontLogo">
                                <label class="block text-sm font-medium text-neutral-700">
                                    Choose logo
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                        class="mt-2 block w-full text-sm text-neutral-600"
                                        @change="logoForm.storefront_logo = ($event.target as HTMLInputElement).files?.[0] ?? null"
                                    />
                                </label>
                                <button
                                    type="submit"
                                    class="min-h-10 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60"
                                    :disabled="!logoForm.storefront_logo || logoForm.processing"
                                >
                                    Save storefront logo
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex min-h-10 items-center gap-2 rounded-md border border-neutral-300 px-4 py-2 text-sm font-semibold text-neutral-700 hover:border-red-300 hover:text-red-700 disabled:opacity-60"
                                    :disabled="!hasCustomStorefrontLogo || logoForm.processing"
                                    @click="resetStorefrontLogo"
                                >
                                    <Trash2 :size="16" aria-hidden="true" />
                                    Reset to default
                                </button>
                            </form>
                            <p v-if="logoForm.errors.storefront_logo" class="mt-2 text-sm text-red-600">{{ logoForm.errors.storefront_logo }}</p>
                        </div>
                        <div class="flex min-h-[120px] items-center justify-center rounded-md border border-neutral-200 bg-[#171717] p-6">
                            <img :src="storefrontLogoUrl" alt="Current storefront logo" class="max-h-16 w-auto object-contain" />
                        </div>
                    </div>

                    <hr class="border-neutral-200" />

                    <div class="grid gap-6 lg:grid-cols-[1fr_280px] lg:items-start">
                        <div>
                            <h2 class="text-xl font-bold text-neutral-900">Subscribed tenant display</h2>
                            <p class="mt-2 max-w-xl text-sm text-neutral-500">
                                Show each tenant's store name on their online storefront so shoppers know which subscribed workspace they are shopping with.
                            </p>
                            <form class="mt-5 space-y-4" @submit.prevent="saveTenantDisplay">
                                <label class="flex items-center gap-2 text-sm font-medium text-neutral-800">
                                    <input v-model="tenantDisplayForm.enabled" type="checkbox" class="rounded border-neutral-300" />
                                    Show tenant name on storefront
                                </label>
                                <label class="block text-sm font-medium text-neutral-700">
                                    Placement
                                    <select v-model="tenantDisplayForm.placement" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm">
                                        <option value="header">Header (beside logo)</option>
                                        <option value="hero">Hero banner</option>
                                        <option value="both">Header and hero</option>
                                    </select>
                                </label>
                                <label class="block text-sm font-medium text-neutral-700">
                                    Label prefix
                                    <input v-model="tenantDisplayForm.label_prefix" type="text" maxlength="40" placeholder="Shopping at" class="mt-1.5 min-h-10 w-full rounded-md border border-neutral-300 px-3 text-sm" />
                                </label>
                                <button type="submit" class="min-h-10 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60" :disabled="tenantDisplayForm.processing">
                                    Save tenant display
                                </button>
                            </form>
                        </div>
                        <div class="rounded-md border border-neutral-200 bg-[#171717] p-5 text-white">
                            <p class="text-[10px] font-bold tracking-widest text-neutral-400 uppercase">Preview</p>
                            <div v-if="tenantDisplayForm.enabled" class="mt-4 space-y-4">
                                <div v-if="tenantDisplayForm.placement === 'header' || tenantDisplayForm.placement === 'both'" class="flex items-center gap-3 border-b border-white/10 pb-4">
                                    <img :src="storefrontLogoUrl" alt="" class="h-8 w-auto object-contain" />
                                    <div class="border-l border-white/20 pl-3">
                                        <p class="text-[10px] uppercase tracking-wide text-neutral-400">{{ tenantDisplayForm.label_prefix || 'Shopping at' }}</p>
                                        <p class="text-sm font-bold">{{ previewTenantName }}</p>
                                    </div>
                                </div>
                                <div v-if="tenantDisplayForm.placement === 'hero' || tenantDisplayForm.placement === 'both'" class="rounded-lg bg-white/5 px-3 py-2 text-sm text-white/85">
                                    {{ tenantDisplayForm.label_prefix || 'Shopping at' }} <strong class="text-white">{{ previewTenantName }}</strong>
                                </div>
                            </div>
                            <p v-else class="mt-4 text-sm text-neutral-400">Tenant name hidden on storefront.</p>
                        </div>
                    </div>

                    <hr class="border-neutral-200" />

                    <div class="grid gap-6 lg:grid-cols-[1fr_280px] lg:items-center">
                        <div>
                            <h2 class="text-xl font-bold text-neutral-900">Workspace dashboard wallpaper</h2>
                            <p class="mt-2 max-w-xl text-sm text-neutral-500">Upload the background image displayed on the workspace launcher at <code>/dashboard</code>. JPG, PNG, or WebP up to 5 MB.</p>
                            <form class="mt-5 flex flex-wrap items-end gap-3" @submit.prevent="uploadDashboardWallpaper">
                                <label class="block text-sm font-medium text-neutral-700">Choose image<input type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-neutral-600" @change="dashboardWallpaperForm.dashboard_wallpaper = ($event.target as HTMLInputElement).files?.[0] ?? null" /></label>
                                <button type="submit" class="min-h-10 rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60" :disabled="!dashboardWallpaperForm.dashboard_wallpaper || dashboardWallpaperForm.processing">Save dashboard wallpaper</button>
                            </form>
                            <p v-if="dashboardWallpaperForm.errors.dashboard_wallpaper" class="mt-2 text-sm text-red-600">{{ dashboardWallpaperForm.errors.dashboard_wallpaper }}</p>
                        </div>
                        <div class="aspect-video overflow-hidden rounded-md bg-neutral-100">
                            <img v-if="dashboardWallpaperUrl" :src="dashboardWallpaperUrl" alt="Current dashboard wallpaper" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full items-center justify-center px-5 text-center text-sm text-neutral-500">No custom dashboard wallpaper uploaded yet.</div>
                        </div>
                    </div>

                    <hr class="border-neutral-200" />

                    <div class="grid gap-6 lg:grid-cols-[1fr_280px] lg:items-center">
                        <div>
                            <h2 class="text-xl font-bold text-neutral-900">Login wallpaper</h2>
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
            </div>
        </div>
    </main>
</template>
