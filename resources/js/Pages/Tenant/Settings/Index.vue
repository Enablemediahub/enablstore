<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Image, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

type StorefrontHero = {
    title: string;
    subtitle: string;
    badge: string;
    imageUrl: string;
    hasCustomImage: boolean;
};

type StorefrontConfig = {
    store_name: string;
    delivery_locations: string;
    default_delivery_location: string;
    delivery_message: string;
    hero_delivery_message: string;
    new_arrivals_days: number;
    customer_service_title: string;
    customer_service_phone: string;
    customer_service_email: string;
    customer_service_hours: string;
    customer_service_message: string;
};

const props = defineProps<{
    catalogueMode: 'shared' | 'separate_online';
    storefrontHero: StorefrontHero;
    storefrontConfig: StorefrontConfig;
}>();

const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);

const form = useForm({
    catalogue_mode: props.catalogueMode,
    storefront_hero_title: props.storefrontHero.title,
    storefront_hero_subtitle: props.storefrontHero.subtitle,
    storefront_hero_badge: props.storefrontHero.badge,
    storefront_store_name: props.storefrontConfig.store_name,
    storefront_delivery_locations: props.storefrontConfig.delivery_locations,
    storefront_default_delivery_location: props.storefrontConfig.default_delivery_location,
    storefront_delivery_message: props.storefrontConfig.delivery_message,
    storefront_hero_delivery_message: props.storefrontConfig.hero_delivery_message,
    storefront_new_arrivals_days: props.storefrontConfig.new_arrivals_days,
    storefront_customer_service_title: props.storefrontConfig.customer_service_title,
    storefront_customer_service_phone: props.storefrontConfig.customer_service_phone,
    storefront_customer_service_email: props.storefrontConfig.customer_service_email,
    storefront_customer_service_hours: props.storefrontConfig.customer_service_hours,
    storefront_customer_service_message: props.storefrontConfig.customer_service_message,
});

const heroImageForm = useForm<{ storefront_hero_image: File | null }>({
    storefront_hero_image: null,
});

const submit = (): void => form.patch(route('tenant.settings.update', { tenant }));

const uploadHeroImage = (): void => {
    heroImageForm.post(route('tenant.settings.hero-image', { tenant }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => heroImageForm.reset('storefront_hero_image'),
    });
};

const removeHeroImage = (): void => {
    heroImageForm.delete(route('tenant.settings.hero-image.destroy', { tenant }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Settings" />
    <main class="min-h-screen bg-neutral-50">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel
                :tenant="tenant"
                current="settings"
                :mobile-open="mobilePanelOpen"
                @close="mobilePanelOpen = false"
            />
            <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <button
                    type="button"
                    class="mb-5 inline-flex rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm lg:hidden"
                    @click="mobilePanelOpen = true"
                >
                    Open admin navigation
                </button>

                <div class="mx-auto max-w-3xl space-y-8">
                    <header>
                        <p class="text-sm font-semibold text-red-700">Admin center</p>
                        <h1 class="mt-1 text-3xl font-bold text-neutral-900">Store settings</h1>
                        <p class="mt-2 text-sm text-neutral-500">
                            Manage your online storefront appearance and catalogue behaviour.
                        </p>
                    </header>

                    <EnCard>
                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="flex items-start gap-3">
                                <Image :size="22" class="mt-0.5 shrink-0 text-[#e21b23]" aria-hidden="true" />
                                <div>
                                    <h2 class="text-lg font-semibold text-neutral-900">Storefront hero banner</h2>
                                    <p class="mt-1 text-sm text-neutral-500">
                                        Customize the hero image and headline shown at the top of your online store.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-6 lg:grid-cols-[1fr_280px] lg:items-start">
                                <div class="space-y-4">
                                    <EnInput
                                        id="storefront_hero_badge"
                                        v-model="form.storefront_hero_badge"
                                        label="Badge text"
                                        placeholder="In stock and ready to ship"
                                    />
                                    <EnInput
                                        id="storefront_hero_title"
                                        v-model="form.storefront_hero_title"
                                        label="Headline"
                                        placeholder="Everyday essentials, delivered simply."
                                    />
                                    <label class="block">
                                        <span class="text-sm font-medium text-neutral-700">Subheadline</span>
                                        <textarea
                                            v-model="form.storefront_hero_subtitle"
                                            rows="3"
                                            class="mt-1.5 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-[#e21b23] focus:outline-none focus:ring-2 focus:ring-[#e21b23]/20"
                                            placeholder="Shop trusted products for your home, pantry, and daily routine."
                                        />
                                    </label>

                                    <div class="rounded-xl border border-dashed border-neutral-300 bg-neutral-50 p-4">
                                        <label class="block text-sm font-medium text-neutral-700">
                                            Hero image
                                            <input
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="mt-2 block w-full text-sm text-neutral-600 file:mr-3 file:rounded-md file:border-0 file:bg-[#e21b23] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#b9151b]"
                                                @change="heroImageForm.storefront_hero_image = ($event.target as HTMLInputElement).files?.[0] ?? null"
                                            />
                                        </label>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <EnButton
                                                type="button"
                                                :loading="heroImageForm.processing"
                                                :disabled="!heroImageForm.storefront_hero_image"
                                                @click="uploadHeroImage"
                                            >
                                                Upload hero image
                                            </EnButton>
                                            <button
                                                type="button"
                                                class="inline-flex min-h-10 items-center gap-2 rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-semibold text-neutral-700 hover:border-red-300 hover:text-red-700 disabled:opacity-50"
                                                :disabled="heroImageForm.processing || !storefrontHero.hasCustomImage"
                                                @click="removeHeroImage"
                                            >
                                                <Trash2 :size="16" aria-hidden="true" />
                                                Remove custom image
                                            </button>
                                        </div>
                                        <p v-if="heroImageForm.errors.storefront_hero_image" class="mt-2 text-sm text-red-600">
                                            {{ heroImageForm.errors.storefront_hero_image }}
                                        </p>
                                        <p class="mt-2 text-xs text-neutral-500">
                                            Recommended: 1600×640px or wider. JPG, PNG, or WebP up to 5 MB.
                                        </p>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-100 shadow-sm">
                                    <div class="border-b border-neutral-200 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        Preview
                                    </div>
                                    <div class="relative aspect-[5/2]">
                                        <img
                                            :src="storefrontHero.imageUrl"
                                            alt="Storefront hero preview"
                                            class="h-full w-full object-cover"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent" />
                                        <div class="absolute inset-0 flex flex-col justify-end p-4">
                                            <span class="inline-flex w-fit rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white backdrop-blur-sm">
                                                {{ form.storefront_hero_badge || 'Badge' }}
                                            </span>
                                            <p class="mt-2 line-clamp-2 text-sm font-black leading-tight text-white">
                                                {{ form.storefront_hero_title || 'Headline' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-neutral-200" />

                            <div>
                                <h2 class="text-lg font-semibold text-neutral-900">Storefront navigation &amp; delivery</h2>
                                <p class="mt-1 text-sm text-neutral-500">
                                    Control the store name, delivery areas, nav messages, and customer service details shown on your online store.
                                </p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <EnInput
                                    id="storefront_store_name"
                                    v-model="form.storefront_store_name"
                                    label="Store display name"
                                    placeholder="Leave blank to use your workspace name"
                                />
                                <EnInput
                                    id="storefront_default_delivery_location"
                                    v-model="form.storefront_default_delivery_location"
                                    label="Default delivery location"
                                    placeholder="Accra"
                                />
                                <label class="block sm:col-span-2">
                                    <span class="text-sm font-medium text-neutral-700">Delivery locations (one per line)</span>
                                    <textarea
                                        v-model="form.storefront_delivery_locations"
                                        rows="4"
                                        class="mt-1.5 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-[#e21b23] focus:outline-none focus:ring-2 focus:ring-[#e21b23]/20"
                                        placeholder="Accra&#10;Kumasi&#10;Tema"
                                    />
                                </label>
                                <EnInput
                                    id="storefront_delivery_message"
                                    v-model="form.storefront_delivery_message"
                                    label="Nav delivery message"
                                    placeholder="Fast local delivery on every order"
                                />
                                <EnInput
                                    id="storefront_hero_delivery_message"
                                    v-model="form.storefront_hero_delivery_message"
                                    label="Hero delivery message"
                                    placeholder="Free local delivery on every order"
                                />
                                <EnInput
                                    id="storefront_new_arrivals_days"
                                    v-model="form.storefront_new_arrivals_days"
                                    type="number"
                                    label="New arrivals window (days)"
                                    placeholder="30"
                                />
                            </div>

                            <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4">
                                <h3 class="text-sm font-semibold text-neutral-900">Customer service</h3>
                                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <EnInput id="storefront_customer_service_title" v-model="form.storefront_customer_service_title" label="Title" />
                                    <EnInput id="storefront_customer_service_phone" v-model="form.storefront_customer_service_phone" label="Phone" />
                                    <EnInput id="storefront_customer_service_email" v-model="form.storefront_customer_service_email" label="Email" />
                                    <EnInput id="storefront_customer_service_hours" v-model="form.storefront_customer_service_hours" label="Hours" />
                                    <label class="block sm:col-span-2">
                                        <span class="text-sm font-medium text-neutral-700">Support message</span>
                                        <textarea
                                            v-model="form.storefront_customer_service_message"
                                            rows="3"
                                            class="mt-1.5 w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm text-neutral-900 shadow-sm focus:border-[#e21b23] focus:outline-none focus:ring-2 focus:ring-[#e21b23]/20"
                                        />
                                    </label>
                                </div>
                            </div>

                            <hr class="border-neutral-200" />

                            <div>
                                <h2 class="text-lg font-semibold text-neutral-900">Online catalogue mode</h2>
                                <p class="mt-1 text-sm text-neutral-500">
                                    Choose whether the Online Store uses the same catalogue as POS or a separate online-only catalogue.
                                </p>
                            </div>

                            <label
                                class="block rounded-lg border p-4"
                                :class="form.catalogue_mode === 'shared' ? 'border-red-600 bg-red-50' : 'border-neutral-200'"
                            >
                                <input v-model="form.catalogue_mode" type="radio" value="shared" class="mr-2" />
                                Shared products
                                <span class="block pl-6 text-sm text-neutral-500">
                                    Products marked for Online Store can also be sold through POS.
                                </span>
                            </label>

                            <label
                                class="block rounded-lg border p-4"
                                :class="form.catalogue_mode === 'separate_online' ? 'border-red-600 bg-red-50' : 'border-neutral-200'"
                            >
                                <input v-model="form.catalogue_mode" type="radio" value="separate_online" class="mr-2" />
                                Separate Online Store products
                                <span class="block pl-6 text-sm text-neutral-500">
                                    Only products not enabled for POS appear in the Online Store.
                                </span>
                            </label>

                            <EnButton type="submit" :loading="form.processing">Save store settings</EnButton>
                        </form>
                    </EnCard>
                </div>
            </section>
        </div>
    </main>
</template>
