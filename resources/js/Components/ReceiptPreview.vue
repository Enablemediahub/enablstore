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
        customerName?: string;
        customerPhone?: string;
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

const displayPaymentMethod = (method: string): string =>
    method === 'mobile_money' ? 'Mobile Money' : method.charAt(0).toUpperCase() + method.slice(1);

const issuedAt = new Intl.DateTimeFormat('en-GH', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date());

const printReceipt = (): void => {
    window.print();
};
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[70] flex items-start justify-center overflow-y-auto bg-neutral-900/60 p-4 sm:items-center print:static print:block print:overflow-visible print:bg-white print:p-0"
    >
        <section
            class="max-h-[calc(100dvh-2rem)] w-full max-w-sm overflow-y-auto rounded-xl bg-white p-5 shadow-lg sm:p-6 print:max-h-none print:max-w-none print:overflow-visible print:rounded-none print:p-0 print:shadow-none"
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
                class="receipt-print receipt-paper mt-5 font-mono text-sm text-neutral-900 print:mt-0"
            >
                <div class="text-center leading-tight">
                    <p class="text-xl font-black tracking-tight">enabl<span class="text-primary-600">store</span></p>
                    <p class="mt-1 text-sm font-bold uppercase">{{ tenant }}</p>
                    <p class="mt-1 text-[11px] text-neutral-500">Sales receipt</p>
                </div>
                <div class="my-4 border-t border-dashed border-neutral-400" />
                <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-neutral-600">
                    <p><span class="text-neutral-500">Date:</span> {{ issuedAt }}</p>
                    <p class="text-right"><span class="text-neutral-500">Receipt:</span> {{ transactionUuid.slice(0, 8).toUpperCase() }}</p>
                    <p v-if="customerName" class="col-span-2"><span class="text-neutral-500">Customer:</span> {{ customerName }}</p>
                    <p v-if="customerPhone" class="col-span-2"><span class="text-neutral-500">Phone:</span> {{ customerPhone }}</p>
                </div>
                <div class="my-4 border-t border-dashed border-neutral-400" />
                <div class="grid grid-cols-[1fr_auto] gap-3 text-[11px] font-bold uppercase text-neutral-500"><span>Item</span><span>Amount</span></div>
                <div class="mt-3 space-y-3">
                    <div
                        v-for="item in items"
                        :key="`${item.name}-${item.priceMinor}`"
                        class="grid grid-cols-[1fr_auto] gap-3"
                    >
                        <div class="min-w-0"><p class="break-words font-semibold leading-5">{{ item.name }}</p><p class="text-xs text-neutral-500">{{ item.quantity }} × {{ formatPrice(item.priceMinor) }}</p></div>
                        <span class="self-start whitespace-nowrap font-semibold">{{ formatPrice(item.priceMinor * item.quantity) }}</span>
                    </div>
                </div>
                <div class="my-4 border-t border-dashed border-neutral-400" />
                <div class="rounded-md bg-neutral-100 px-3 py-3">
                    <div class="flex justify-between text-base font-black"><span>Total paid</span><span>{{ formatPrice(totalMinor) }}</span></div>
                    <div class="mt-1 flex justify-between text-[11px] text-neutral-600"><span>Payment method</span><span class="font-semibold">{{ displayPaymentMethod(paymentMethod) }}</span></div>
                </div>
                <div class="my-4 border-t border-dashed border-neutral-400" />
                <p class="text-center text-xs font-semibold">Thank you for shopping with us.</p>
                <p class="mt-1 text-center text-[10px] text-neutral-500">Please retain this receipt for your records.</p>
                <p class="mt-4 border-t border-dashed border-neutral-400 pt-3 text-center text-[10px] font-semibold tracking-wide text-neutral-600">Powered By Enable Technologies</p>
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

<style>
@media print {
    @page { size: 80mm auto; margin: 0; }

    body * { visibility: hidden; }

    .receipt-print, .receipt-print * { visibility: visible; }

    .receipt-print {
        position: relative;
        width: 72mm;
        margin: 0 auto;
        padding: 4mm;
        color: #000;
        font-size: 11px;
        line-height: 1.3;
    }
}
</style>
