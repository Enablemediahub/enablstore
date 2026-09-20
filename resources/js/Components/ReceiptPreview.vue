<!--
Props:
- open: whether the receipt preview is visible
- tenant: store identifier shown on the receipt
- items: purchased line items
- totalMinor: total amount in GHS minor units
- paymentMethod: selected payment method
- transactionUuid: sale identifier

Emits:
- close: emitted when the preview closes

Slots:
- none
-->
<script setup lang="ts">
import { Printer, X } from '@lucide/vue';

withDefaults(
    defineProps<{
        open?: boolean;
        tenant: string;
        items: Array<{ name: string; quantity: number; priceMinor: number }>;
        totalMinor: number;
        paymentMethod: string;
        transactionUuid: string;
    }>(),
    {
        open: false,
    },
);

const emit = defineEmits<{
    close: [];
}>();

const formatPrice = (minor: number): string =>
    new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
    }).format(minor / 100);

const printReceipt = (): void => {
    window.print();
};
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-neutral-900/60 p-4 print:static print:block print:bg-white print:p-0"
    >
        <section
            class="w-full max-w-sm rounded-lg bg-white p-6 shadow-lg print:max-w-none print:rounded-none print:p-0 print:shadow-none"
            role="dialog"
            aria-modal="true"
            aria-labelledby="receipt-title"
        >
            <div class="flex items-center justify-between print:hidden">
                <h2
                    id="receipt-title"
                    class="text-lg font-semibold text-neutral-900"
                >
                    Receipt preview
                </h2>
                <button
                    type="button"
                    class="rounded-md p-2 text-neutral-500 hover:bg-neutral-100"
                    aria-label="Close receipt preview"
                    @click="emit('close')"
                >
                    <X :size="20" aria-hidden="true" />
                </button>
            </div>
            <div
                class="receipt-paper mt-5 font-mono text-sm text-neutral-900 print:mt-0"
            >
                <div class="text-center">
                    <p class="text-lg font-bold">Enablstore</p>
                    <p class="text-xs text-neutral-500">{{ tenant }}</p>
                    <p class="mt-3 text-xs text-neutral-500">
                        {{ transactionUuid }}
                    </p>
                </div>
                <div class="my-4 border-t border-dashed border-neutral-300" />
                <div class="space-y-3">
                    <div
                        v-for="item in items"
                        :key="`${item.name}-${item.priceMinor}`"
                        class="flex justify-between gap-4"
                    >
                        <span>{{ item.name }} x{{ item.quantity }}</span>
                        <span>{{
                            formatPrice(item.priceMinor * item.quantity)
                        }}</span>
                    </div>
                </div>
                <div class="my-4 border-t border-dashed border-neutral-300" />
                <div class="flex justify-between text-base font-bold">
                    <span>Total</span>
                    <span>{{ formatPrice(totalMinor) }}</span>
                </div>
                <p class="mt-3 text-center text-xs text-neutral-500">
                    Paid by {{ paymentMethod.replace('_', ' ') }}
                </p>
                <p class="mt-5 text-center text-xs text-neutral-500">
                    Thank you for shopping with us.
                </p>
            </div>
            <button
                type="button"
                class="bg-primary-600 hover:bg-primary-700 mt-6 inline-flex w-full items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-semibold text-white print:hidden"
                @click="printReceipt"
            >
                <Printer :size="18" aria-hidden="true" />
                Print receipt
            </button>
        </section>
    </div>
</template>
