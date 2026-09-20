<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LockKeyhole, ShoppingCart } from '@lucide/vue';

defineProps<{ tenant: string }>();

const form = useForm({ name: '', pin: '' });

const unlock = (): void => {
    form.post(route('tenant.pos.unlock', { tenant: route().params.tenant }));
};
</script>

<template>
    <Head title="Unlock POS" />
    <main class="flex min-h-screen items-center justify-center bg-neutral-950 px-5 py-10 text-white">
        <section class="w-full max-w-md rounded-3xl border border-white/10 bg-neutral-900 p-7 shadow-2xl sm:p-10">
            <div class="flex size-14 items-center justify-center rounded-2xl bg-white text-neutral-950">
                <ShoppingCart :size="28" aria-hidden="true" />
            </div>
            <p class="mt-8 text-xs font-bold tracking-[0.2em] text-emerald-400 uppercase">Enablstore POS</p>
            <h1 class="mt-3 text-3xl font-bold">Enter your sales access</h1>
            <p class="mt-3 text-sm leading-6 text-white/60">Use the name and PIN assigned to you by your store administrator.</p>

            <form class="mt-8 space-y-5" @submit.prevent="unlock">
                <div>
                    <label for="pos-name" class="mb-2 block text-sm font-medium text-white/80">Salesperson name</label>
                    <input id="pos-name" v-model="form.name" type="text" autocomplete="name" required autofocus class="min-h-12 w-full rounded-xl border border-white/15 bg-white/5 px-4 text-white placeholder:text-white/30 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" placeholder="Enter your name" />
                    <p v-if="form.errors.name" class="mt-2 text-sm text-red-300">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="pos-pin" class="mb-2 block text-sm font-medium text-white/80">PIN code</label>
                    <input id="pos-pin" v-model="form.pin" type="password" inputmode="numeric" autocomplete="one-time-code" minlength="4" maxlength="6" pattern="[0-9]{4,6}" required class="min-h-12 w-full rounded-xl border border-white/15 bg-white/5 px-4 tracking-[0.35em] text-white placeholder:text-white/30 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" placeholder="••••" />
                    <p v-if="form.errors.pin" class="mt-2 text-sm text-red-300">{{ form.errors.pin }}</p>
                </div>
                <EnButton type="submit" class="min-h-12! w-full! justify-center! bg-emerald-500! text-white! hover:bg-emerald-400!" :disabled="form.processing">
                    <LockKeyhole :size="17" aria-hidden="true" />
                    {{ form.processing ? 'Checking access...' : 'Open POS' }}
                </EnButton>
            </form>
        </section>
    </main>
</template>