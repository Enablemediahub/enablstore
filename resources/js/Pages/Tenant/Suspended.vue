<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle2, LockKeyhole, ShieldAlert } from '@lucide/vue';
import axios from 'axios';
import { computed, ref } from 'vue';

const props = defineProps<{
    tenant: { id: string; name: string };
    accountActive: boolean;
    contactEmail: string | null;
    subscriptionAmountMinor: number | null;
    billingInterval: string;
    billingIntervalMonths: number;
    status?: string | null;
    error?: string | null;
}>();

const paymentModalOpen = ref(false);
const paymentEmail = ref(props.contactEmail ?? '');
const paymentProcessing = ref(false);
const paymentError = ref('');

type Checkout = { reference: string; amount_minor: number; public_key: string; callback_url: string };
type Verification = { status: 'success' | 'error'; message: string; account_active: boolean };

const billingIntervalLabel = computed(() => {
    if (props.billingInterval === 'weekly' || props.billingInterval === 'daily') return props.billingInterval;
    const months = props.billingIntervalMonths;
    if (months === 1) return 'monthly';
    if (months === 3) return 'quarterly';
    if (months === 12) return 'yearly';
    return `every ${months} months`;
});

const formatPrice = (minor: number): string => new Intl.NumberFormat('en-GH', {
    style: 'currency',
    currency: 'GHS',
}).format(minor / 100);

const errorMessage = (error: unknown): string => {
    if (axios.isAxiosError(error)) {
        const data = error.response?.data as { message?: string; errors?: { email?: string[] } } | undefined;
        return data?.errors?.email?.[0] ?? data?.message ?? 'Payment could not be started. Please try again.';
    }

    return error instanceof Error ? error.message : 'Payment could not be started. Please try again.';
};

const openPaystackPopup = async (): Promise<void> => {
    if (!window.PaystackPop) {
        await new Promise<void>((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://js.paystack.co/v1/inline.js';
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Paystack could not be loaded. Check your connection and retry.'));
            document.head.appendChild(script);
        });
    }

    if (!window.PaystackPop) throw new Error('Paystack could not be loaded. Please retry.');
};

const paySubscription = async (): Promise<void> => {
    paymentProcessing.value = true;
    paymentError.value = '';

    try {
        const { data: checkout } = await window.axios.post<Checkout>(
            route('tenant.subscription.checkout', { tenant: props.tenant.id }),
            { email: paymentEmail.value },
        );
        await openPaystackPopup();

        window.PaystackPop?.setup({
            key: checkout.public_key,
            email: paymentEmail.value,
            amount: checkout.amount_minor,
            currency: 'GHS',
            ref: checkout.reference,
            callback: async ({ reference }) => {
                try {
                    const { data: verification } = await window.axios.get<Verification>(checkout.callback_url, {
                        params: { reference },
                    });
                    if (verification.status !== 'success' || !verification.account_active) {
                        throw new Error(verification.message || 'Payment could not be verified. Your workspace remains suspended.');
                    }

                    paymentModalOpen.value = false;
                    router.visit(route('tenant.subscription.suspended', { tenant: props.tenant.id }), { replace: true });
                } catch (error) {
                    paymentError.value = errorMessage(error);
                    paymentProcessing.value = false;
                }
            },
            onClose: () => { paymentProcessing.value = false; },
        }).openIframe();
    } catch (error) {
        paymentError.value = errorMessage(error);
        paymentProcessing.value = false;
    }
};
</script>

<template>
    <Head :title="accountActive ? 'Service restored' : 'Account suspended'" />
    <main class="flex min-h-screen items-center justify-center px-4 py-12 text-neutral-900" :class="accountActive ? 'bg-emerald-950' : 'bg-red-950'">
        <section class="w-full max-w-xl overflow-hidden rounded-lg border bg-white shadow-2xl" :class="accountActive ? 'border-emerald-200' : 'border-red-200'">
            <div class="h-2" :class="accountActive ? 'bg-emerald-700' : 'bg-red-700'" />
            <div class="p-7 sm:p-10">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white p-2 shadow-sm" :class="accountActive ? 'border-emerald-600 ring-4 ring-emerald-50' : 'border-red-600 ring-4 ring-red-50'">
                            <img src="/images/Enablstore-cropped.png" alt="Enablstore logo" class="h-full w-full rounded-full object-contain" />
                        </div>
                        <div>
                            <p class="text-sm font-black tracking-wide text-neutral-900">Enablstore</p>
                            <p class="mt-0.5 text-xs text-neutral-500">Subscriber services</p>
                        </div>
                    </div>
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-full" :class="accountActive ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'">
                        <CheckCircle2 v-if="accountActive" :size="28" aria-hidden="true" />
                        <ShieldAlert v-else :size="28" aria-hidden="true" />
                    </div>
                </div>
                <p class="mt-7 text-xs font-bold uppercase tracking-[0.18em]" :class="accountActive ? 'text-emerald-800' : 'text-red-800'">{{ tenant.name }}</p>
                <h1 class="mt-2 text-3xl font-black">{{ accountActive ? 'Service restored' : 'Account suspended' }}</h1>
                <p class="mt-4 text-sm leading-6 text-neutral-600">{{ accountActive ? 'Your payment has been verified and the complete workspace is active again.' : 'This subscriber workspace is temporarily unavailable. Complete the subscription payment to restore service across your portals.' }}</p>
                <p v-if="status" class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ status }}</p>
                <p v-if="error" class="mt-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ error }}</p>

                <div v-if="!accountActive" class="mt-7 border-t border-neutral-200 pt-6">
                    <div class="flex items-end justify-between gap-4"><div><p class="text-sm font-semibold text-neutral-700">Subscription renewal</p><p class="mt-1 text-xs capitalize text-neutral-500">{{ billingIntervalLabel }} billing · paid to Enablstore</p></div><strong v-if="subscriptionAmountMinor" class="font-mono text-xl">{{ formatPrice(subscriptionAmountMinor) }}</strong><span v-else class="text-sm font-semibold text-red-800">Contact support for the renewal amount</span></div>
                    <button type="button" class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md px-5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" :class="accountActive ? 'bg-emerald-700 hover:bg-emerald-800' : 'bg-red-700 hover:bg-red-800'" :disabled="!subscriptionAmountMinor" @click="paymentModalOpen = true"><LockKeyhole :size="17" aria-hidden="true" /> Continue with Paystack</button>
                </div>

                <div v-else class="mt-7 flex flex-wrap gap-3 border-t border-neutral-200 pt-6"><Link href="/login" class="inline-flex min-h-11 items-center justify-center rounded-md bg-emerald-700 px-5 text-sm font-bold text-white hover:bg-emerald-800">Admin login</Link><Link href="/dashboard" class="inline-flex min-h-11 items-center justify-center rounded-md border border-neutral-300 px-5 text-sm font-semibold text-neutral-700 hover:bg-neutral-50">Workspace landing</Link></div>
            </div>
        </section>

        <Modal :show="paymentModalOpen" max-width="md" @close="paymentModalOpen = false">
            <form class="space-y-5 p-6" @submit.prevent="paySubscription">
                <div><p class="text-xs font-bold uppercase tracking-wide text-red-800">Enablstore payment</p><h2 class="mt-1 text-xl font-bold">Restore {{ tenant.name }}</h2><p class="mt-2 text-sm text-neutral-500">Pay {{ formatPrice(subscriptionAmountMinor ?? 0) }} to Enablstore through Paystack. Service resumes after payment is verified.</p></div>
                <label class="block text-sm font-medium text-neutral-700">Payment receipt email<input v-model="paymentEmail" type="email" required autocomplete="email" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" placeholder="you@example.com" /></label>
                <p v-if="paymentError" class="text-sm text-red-700">{{ paymentError }}</p>
                <div class="flex justify-end gap-3 border-t border-neutral-100 pt-4"><button type="button" class="min-h-10 rounded-md border border-neutral-300 px-4 text-sm font-semibold text-neutral-700" :disabled="paymentProcessing" @click="paymentModalOpen = false">Cancel</button><EnButton type="submit" :loading="paymentProcessing">Pay securely</EnButton></div>
            </form>
        </Modal>
    </main>
</template>
