<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole, ShoppingCart } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps<{ tenant: string; workspace: 'store' | 'pos'; wallpaperUrl?: string | null }>();
const form = useForm({ username: '', pin: '' });
const pinVisible = ref(false);

const unlock = (): void => {
    form.post(route('tenant.pos.unlock', { tenant: props.tenant }));
};
</script>

<template>
    <Head :title="workspace === 'store' ? 'Unlock Online Store' : 'Unlock POS'" />
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-neutral-950 bg-cover bg-center px-5 py-10 text-white" :style="props.wallpaperUrl ? { backgroundImage: `url('${props.wallpaperUrl}')` } : undefined">
        <div class="absolute inset-0 bg-neutral-950/75" aria-hidden="true" />
        <section class="relative z-10 w-full max-w-md rounded-3xl border border-white/10 bg-neutral-900/90 p-7 shadow-2xl backdrop-blur-sm sm:p-10">
            <div class="flex size-14 items-center justify-center rounded-2xl bg-white text-neutral-950"><ShoppingCart :size="28" aria-hidden="true" /></div>
            <p class="mt-8 text-xs font-bold tracking-[0.2em] text-emerald-400 uppercase">Enablstore {{ workspace === 'store' ? 'Online Store' : 'POS' }}</p>
            <h1 class="mt-3 text-3xl font-bold">Enter your sales access</h1>
            <p class="mt-3 text-sm leading-6 text-white/60">Use your assigned username, including the subscriber code prefix, and your POS PIN.</p>
            <form class="mt-8 space-y-5" @submit.prevent="unlock">
                <div>
                    <label for="pos-name" class="mb-2 block text-sm font-medium text-white/80">Username</label>
                    <input id="pos-name" v-model="form.username" type="text" autocomplete="username" required autofocus class="min-h-12 w-full rounded-xl border border-white/15 bg-white/5 px-4 text-white placeholder:text-white/30 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" placeholder="ES001-regina" />
                    <p v-if="form.errors.username" class="mt-2 text-sm text-red-300">{{ form.errors.username }}</p>
                </div>
                <div>
                    <label for="pos-pin" class="mb-2 block text-sm font-medium text-white/80">PIN code</label>
                    <div class="relative">
                        <input id="pos-pin" v-model="form.pin" :type="pinVisible ? 'text' : 'password'" inputmode="numeric" autocomplete="one-time-code" minlength="4" maxlength="6" pattern="[0-9]{4,6}" required class="min-h-12 w-full rounded-xl border border-white/15 bg-white/5 px-4 pr-12 tracking-[0.35em] text-white placeholder:text-white/30 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/20" placeholder="••••" />
                        <button type="button" class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center text-white/60 hover:text-white" :aria-label="pinVisible ? 'Hide PIN' : 'Show PIN'" @click="pinVisible = !pinVisible">
                            <EyeOff v-if="pinVisible" :size="19" aria-hidden="true" />
                            <Eye v-else :size="19" aria-hidden="true" />
                        </button>
                    </div>
                    <p v-if="form.errors.pin" class="mt-2 text-sm text-red-300">{{ form.errors.pin }}</p>
                </div>
                <EnButton type="submit" class="min-h-12! w-full! justify-center! bg-emerald-500! text-white! hover:bg-emerald-400!" :disabled="form.processing">
                    <LockKeyhole :size="17" aria-hidden="true" />
                    {{ form.processing ? 'Checking access...' : `Open ${workspace === 'store' ? 'Online Store' : 'POS'}` }}
                </EnButton>
            </form>
        </section>
    </main>
</template>
