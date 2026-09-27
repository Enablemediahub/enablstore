<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import SuperAdminSidePanel from '@/Components/SuperAdminSidePanel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Check, Pencil, Plus, X } from '@lucide/vue';
import { nextTick, ref } from 'vue';

type Plan = {
    id: number;
    name: string;
    description: string | null;
    price_minor: number;
    currency: string;
    billing_interval_months: number;
    features: string[];
    is_active: boolean;
};

const { plans, status } = defineProps<{ plans: Plan[]; status?: string | null }>();
const editingId = ref<number | null>(null);
const planEditor = ref<HTMLElement | null>(null);
const form = useForm({ name: '', description: '', price_ghs: '', billing_interval_months: 1, features: ['pos', 'online_store'], is_active: true });
const featureOptions = [
    { value: 'pos', label: 'Point of Sale' },
    { value: 'online_store', label: 'Online Store' },
    { value: 'restaurant_foodstore', label: 'FoodStore' },
    { value: 'products', label: 'Products' },
    { value: 'inventory', label: 'Inventory' },
];

const billingLabel = (months: number): string => ({ 1: 'Monthly', 3: 'Quarterly', 6: 'Every 6 months', 12: 'Yearly' })[months] ?? `Every ${months} months`;
const formatPrice = (minor: number, currency: string): string => new Intl.NumberFormat('en-GH', { style: 'currency', currency }).format(minor / 100);

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
};

const editPlan = async (plan: Plan): Promise<void> => {
    editingId.value = plan.id;
    form.name = plan.name;
    form.description = plan.description ?? '';
    form.price_ghs = String(plan.price_minor / 100);
    form.billing_interval_months = plan.billing_interval_months;
    form.features = [...plan.features];
    form.is_active = plan.is_active;
    form.clearErrors();
    await nextTick();
    planEditor.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    document.getElementById('plan-name')?.focus({ preventScroll: true });
};

const submit = (): void => {
    if (editingId.value === null) {
        form.post(route('super-admin.subscriptions.plans.store'), { preserveScroll: true, onSuccess: resetForm });
        return;
    }

    form.patch(route('super-admin.subscriptions.plans.update', { plan: editingId.value }), { preserveScroll: true, onSuccess: resetForm });
};
</script>

<template>
    <Head title="Subscription settings" />
    <main class="flex min-h-screen bg-neutral-50 text-neutral-900">
        <SuperAdminSidePanel current="subscriptions" />
        <section class="min-w-0 flex-1 px-5 py-8 sm:px-10">
            <div class="mx-auto max-w-5xl space-y-6">
                <header>
                    <p class="text-sm font-semibold text-[#e21b23]">Billing</p>
                    <h1 class="mt-1 text-3xl font-black">Subscription settings</h1>
                    <p class="mt-2 text-neutral-500">Manage the plans offered during sign-up and subscriber enrollment.</p>
                </header>
                <p v-if="status" class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ status }}</p>

                <EnCard>
                    <div ref="planEditor" tabindex="-1" class="scroll-mt-6">
                    <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-bold">{{ editingId === null ? 'Add a plan' : 'Edit plan' }}</h2><p class="mt-1 text-sm text-neutral-500">Set a GHS price and billing duration in months.</p></div><button v-if="editingId !== null" type="button" class="inline-flex size-9 items-center justify-center rounded-md border border-neutral-300 text-neutral-600" aria-label="Cancel editing" @click="resetForm"><X :size="17" /></button></div>
                    <form class="mt-5 space-y-4" @submit.prevent="submit">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <EnInput id="plan-name" v-model="form.name" label="Plan name" :error="form.errors.name" required />
                            <EnInput id="plan-price" v-model="form.price_ghs" type="number" min="0.01" step="0.01" label="Price (GHS)" :error="form.errors.price_ghs" required />
                        </div>
                        <EnInput id="plan-description" v-model="form.description" label="Description (optional)" :error="form.errors.description" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <EnInput id="plan-months" v-model="form.billing_interval_months" type="number" min="1" max="120" step="1" label="Billing duration (months)" :error="form.errors.billing_interval_months" required />
                            <label class="flex min-h-11 items-center gap-3 self-end rounded-md border border-neutral-200 px-3 text-sm font-medium text-neutral-700"><input v-model="form.is_active" type="checkbox" class="rounded border-neutral-300 text-[#e21b23] focus:ring-[#e21b23]" /> Offer this plan to new subscribers</label>
                        </div>
                        <fieldset>
                            <legend class="mb-2 text-sm font-semibold text-neutral-700">Included access</legend>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <label v-for="feature in featureOptions" :key="feature.value" class="flex items-center gap-2 rounded-md border border-neutral-200 px-3 py-2.5 text-sm text-neutral-700"><input v-model="form.features" type="checkbox" :value="feature.value" class="rounded border-neutral-300 text-[#e21b23] focus:ring-[#e21b23]" />{{ feature.label }}</label>
                            </div>
                        </fieldset>
                        <p v-if="form.errors.features || form.errors['features.0']" class="text-sm text-red-700">{{ form.errors.features || form.errors['features.0'] }}</p>
                        <div class="flex justify-end"><button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-md bg-[#e21b23] px-5 text-sm font-bold text-white hover:bg-[#b9151b] disabled:opacity-60" :disabled="form.processing"><Check v-if="editingId !== null" :size="17" /><Plus v-else :size="17" />{{ form.processing ? 'Saving...' : editingId === null ? 'Create plan' : 'Save changes' }}</button></div>
                    </form>
                    </div>
                </EnCard>

                <EnCard>
                    <div class="flex items-center justify-between gap-4"><div><h2 class="text-lg font-bold">Available plans</h2><p class="mt-1 text-sm text-neutral-500">Inactive plans remain attached to existing subscriptions but are hidden from new enrollments.</p></div><span class="font-mono text-sm text-neutral-500">{{ plans.length }}</span></div>
                    <div v-if="plans.length" class="mt-5 overflow-x-auto"><table class="w-full min-w-[680px] text-left text-sm"><thead class="border-b border-neutral-200 text-xs font-semibold uppercase text-neutral-500"><tr><th class="px-3 py-3">Plan</th><th class="px-3 py-3">Price</th><th class="px-3 py-3">Duration</th><th class="px-3 py-3">Status</th><th class="px-3 py-3 text-right">Edit</th></tr></thead><tbody><tr v-for="plan in plans" :key="plan.id" class="border-b border-neutral-100 last:border-0"><td class="px-3 py-4"><p class="font-semibold">{{ plan.name }}</p><p v-if="plan.description" class="mt-1 max-w-sm text-xs text-neutral-500">{{ plan.description }}</p></td><td class="px-3 py-4 font-mono">{{ formatPrice(plan.price_minor, plan.currency) }}</td><td class="px-3 py-4">{{ billingLabel(plan.billing_interval_months) }}</td><td class="px-3 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="plan.is_active ? 'bg-green-100 text-green-800' : 'bg-neutral-100 text-neutral-600'">{{ plan.is_active ? 'Offered' : 'Inactive' }}</span></td><td class="px-3 py-4 text-right"><button type="button" class="inline-flex min-h-9 items-center gap-2 rounded-md border border-neutral-300 px-3 text-xs font-bold hover:border-[#e21b23]" @click="editPlan(plan)"><Pencil :size="14" aria-hidden="true" />Edit</button></td></tr></tbody></table></div>
                    <p v-else class="mt-5 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">No plans exist yet. Create one to enable subscriber sign-up and enrollment.</p>
                </EnCard>
            </div>
        </section>
    </main>
</template>