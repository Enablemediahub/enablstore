<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps<{ tenant: string; subscriberCode: string; usernameHint: string; workspace: 'store' | 'pos' | 'foodstore'; wallpaperUrl?: string | null }>();
const form = useForm({ username: '', pin: '' });
const pinVisible = ref(false);

const unlock = (): void => {
    form.post(route('tenant.pos.unlock', { tenant: props.tenant }));
};
</script>

<template>
    <Head :title="workspace === 'store' ? 'Unlock Online Store' : workspace === 'foodstore' ? 'Unlock FoodStore' : 'Unlock POS'" />
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-neutral-950 bg-cover bg-center px-5 py-10 text-white" :style="props.wallpaperUrl ? { backgroundImage: `url('${props.wallpaperUrl}')` } : undefined">
        <div class="absolute inset-0 bg-[#171717]/65" aria-hidden="true" />
        <section class="relative z-10 w-full max-w-md overflow-hidden rounded-3xl border border-white/80 bg-white/95 p-7 text-[#171717] shadow-2xl backdrop-blur-sm sm:p-10">
            <div class="flex items-center gap-4">
                <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 bg-white p-2 shadow-sm ring-4" :class="workspace === 'foodstore' ? 'border-emerald-700 ring-emerald-50' : 'border-[#e21b23] ring-red-50'">
                    <img src="/images/Enablstore-cropped.png" alt="Enablstore" class="h-full w-full object-contain" />
                </div>
                <div>
                    <p class="text-xs font-bold tracking-[0.18em] uppercase" :class="workspace === 'foodstore' ? 'text-emerald-800' : 'text-[#e21b23]'">Enablstore</p>
                    <p class="mt-1 text-sm font-semibold text-neutral-500">Salesperson access</p>
                </div>
            </div>
            <p class="mt-8 text-xs font-bold tracking-[0.2em] uppercase" :class="workspace === 'foodstore' ? 'text-emerald-800' : 'text-[#e21b23]'">{{ workspace === 'store' ? 'Online Store' : workspace === 'foodstore' ? 'FoodStore' : 'Point of Sale' }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight">{{ workspace === 'foodstore' ? 'Enter your staff access' : 'Enter your sales access' }}</h1>
            <p class="mt-3 text-sm leading-6 text-neutral-600">Use a cashier account username and its assigned PIN. Admin passwords do not work here.</p>
            <form class="mt-8 space-y-5" @submit.prevent="unlock">
                <div>
                    <label for="pos-name" class="mb-2 block text-sm font-semibold text-neutral-700">Username</label>
                    <input id="pos-name" v-model="form.username" type="text" autocomplete="username" required autofocus class="min-h-12 w-full rounded-xl border border-neutral-300 bg-white px-4 text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2" :class="workspace === 'foodstore' ? 'focus:border-emerald-700 focus:ring-emerald-700/20' : 'focus:border-[#e21b23] focus:ring-[#e21b23]/20'" :placeholder="usernameHint" />
                    <p v-if="form.errors.username" class="mt-2 text-sm text-red-700">{{ form.errors.username }}</p>
                </div>
                <div>
                    <label for="pos-pin" class="mb-2 block text-sm font-semibold text-neutral-700">PIN code</label>
                    <div class="relative">
                        <input id="pos-pin" v-model="form.pin" :type="pinVisible ? 'text' : 'password'" inputmode="numeric" autocomplete="one-time-code" minlength="4" maxlength="6" pattern="[0-9]{4,6}" required class="min-h-12 w-full rounded-xl border border-neutral-300 bg-white px-4 pr-12 tracking-[0.35em] text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2" :class="workspace === 'foodstore' ? 'focus:border-emerald-700 focus:ring-emerald-700/20' : 'focus:border-[#e21b23] focus:ring-[#e21b23]/20'" placeholder="••••" />
                        <button type="button" class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center text-neutral-500" :class="workspace === 'foodstore' ? 'hover:text-emerald-800' : 'hover:text-[#e21b23]'" :aria-label="pinVisible ? 'Hide PIN' : 'Show PIN'" @click="pinVisible = !pinVisible">
                            <EyeOff v-if="pinVisible" :size="19" aria-hidden="true" />
                            <Eye v-else :size="19" aria-hidden="true" />
                        </button>
                    </div>
                    <p v-if="form.errors.pin" class="mt-2 text-sm text-red-700">{{ form.errors.pin }}</p>
                </div>
                <EnButton type="submit" class="min-h-12! w-full! justify-center! text-white!" :class="workspace === 'foodstore' ? 'bg-emerald-700! hover:bg-emerald-800!' : 'bg-[#e21b23]! hover:bg-[#b9151b]!'" :disabled="form.processing">
                    <LockKeyhole :size="17" aria-hidden="true" />
                    {{ form.processing ? 'Checking access...' : `Open ${workspace === 'store' ? 'Online Store' : workspace === 'foodstore' ? 'FoodStore' : 'POS'}` }}
                </EnButton>
            </form>
        </section>
    </main>
</template>
