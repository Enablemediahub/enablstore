<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

type Plan = { id: number; name: string; description: string | null; price_minor: number; currency: string; billing_interval_months: number };

const props = defineProps<{ plans: Plan[] }>();

const form = useForm({
    business_name: '',
    slug: '',
    email: '',
    phone: '',
    owner_name: '',
    plan_id: props.plans[0]?.id ?? '',
});

const billingLabel = (months: number): string => ({ 1: 'monthly', 3: 'quarterly', 6: 'every 6 months', 12: 'yearly' })[months] ?? `every ${months} months`;
const formatPrice = (minor: number, currency: string): string => new Intl.NumberFormat('en-GH', { style: 'currency', currency }).format(minor / 100);

const submit = (): void => {
    form.post(route('tenant.register.store'));
};
</script>

<template>
    <Head title="Create your store" />
    <main class="min-h-screen bg-neutral-50 px-4 py-12 sm:px-8">
        <div class="mx-auto max-w-xl">
            <div class="mb-8">
                <p
                    class="text-primary-700 text-sm font-semibold tracking-wide uppercase"
                >
                    Enablstore
                </p>
                <h1
                    class="mt-2 text-3xl font-bold tracking-tight text-neutral-900"
                >
                    Create your store
                </h1>
                <p class="mt-3 text-neutral-500">
                    Set up your Ghanaian retail workspace and start with a
                    14-day trial.
                </p>
            </div>

            <EnCard>
                <form class="space-y-5" @submit.prevent="submit">
                    <EnInput
                        id="business-name"
                        v-model="form.business_name"
                        label="Business name"
                        :error="form.errors.business_name"
                        required
                    />
                    <EnInput
                        id="slug"
                        v-model="form.slug"
                        label="Store URL name"
                        help-text="Use letters, numbers, underscores, or dashes."
                        :error="form.errors.slug"
                        required
                    />
                    <EnInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        label="Business email"
                        :error="form.errors.email"
                        required
                    />
                    <EnInput
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        label="Phone number"
                        :error="form.errors.phone"
                    />
                    <EnInput
                        id="owner-name"
                        v-model="form.owner_name"
                        label="Your name"
                        :error="form.errors.owner_name"
                        required
                    />
                    <label class="block text-sm font-medium text-neutral-700">Subscription plan<select id="plan-id" v-model="form.plan_id" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }} · {{ formatPrice(plan.price_minor, plan.currency) }} / {{ billingLabel(plan.billing_interval_months) }}</option></select></label>
                    <p v-if="form.errors.plan_id" class="text-sm text-red-700">{{ form.errors.plan_id }}</p>
                    <p v-if="plans.length === 0" class="rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">Online sign-up is temporarily unavailable. Contact Enablstore to set up your workspace.</p>
                    <EnButton
                        type="submit"
                        :loading="form.processing"
                        class="w-full"
                        :disabled="plans.length === 0"
                    >
                        Create store
                    </EnButton>
                </form>
            </EnCard>
        </div>
    </main>
</template>
