<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import EnButton from '@/Components/EnButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Eye, EyeOff } from '@lucide/vue';
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    canResetPassword?: boolean;
    status?: string;
    wallpaperUrl?: string | null;
}>();

const form = useForm({
    username: '',
    password: '',
    remember: false,
});
const passwordRevealed = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Log in" />
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#171717] px-4 py-10 sm:px-8">
        <div class="absolute inset-0 bg-cover bg-center" :style="{ backgroundImage: `url('${props.wallpaperUrl ?? '/images/products/catalogue.svg'}')` }" aria-hidden="true" />
        <div class="absolute inset-0 bg-[linear-gradient(110deg,#171717e8_0%,#171717bb_48%,#e21b23b8_100%)]" aria-hidden="true" />
        <div class="relative z-10 grid w-full max-w-6xl items-center gap-12 lg:grid-cols-[1fr_430px]">
            <div class="hidden text-white lg:block">
                <div class="flex size-32 items-center justify-center overflow-hidden rounded-full border-4 border-[#e21b23] bg-white p-4 shadow-2xl ring-8 ring-white/15">
                    <img src="/images/Enablstore-cropped.png" alt="Enablstore" class="h-full w-full object-contain" />
                </div>
                <p class="mt-8 text-sm font-bold tracking-[0.2em] text-[#ff8b8f] uppercase">Your retail workspace</p>
                <h1 class="mt-4 max-w-xl text-5xl font-black leading-[1.05] tracking-tight">Everything your shop needs, in one place.</h1>
                <p class="mt-6 max-w-lg text-lg leading-8 text-white/75">Sign in to choose your Online Store or Point of Sale workspace.</p>
            </div>
            <section class="rounded-[2rem] border border-white/70 bg-white/95 p-7 shadow-2xl backdrop-blur sm:p-10">
                <div class="flex items-center gap-4 lg:hidden"><div class="flex size-16 items-center justify-center overflow-hidden rounded-full border-2 border-[#e21b23] bg-white p-2 shadow-md ring-4 ring-red-50"><img src="/images/Enablstore-cropped.png" alt="Enablstore" class="h-full w-full object-contain" /></div><span class="text-sm font-bold text-[#e21b23]">Retail workspace</span></div>
                <p class="mt-5 text-xs font-bold tracking-[0.18em] text-[#e21b23] uppercase">Welcome back</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-[#171717]">Sign in to continue</h2>
                <div v-if="status" class="mt-4 text-sm font-medium text-green-600">{{ status }}</div>
                <form class="mt-7" @submit.prevent="submit">
                    <div><InputLabel for="username" value="Username" /><TextInput id="username" v-model="form.username" type="text" class="mt-1 block w-full" required autofocus autocomplete="username" /><InputError class="mt-2" :message="form.errors.username" /></div>
                    <div class="mt-4"><InputLabel for="password" value="Password" /><div class="relative"><TextInput id="password" v-model="form.password" :type="passwordRevealed ? 'text' : 'password'" class="mt-1 block w-full pr-12" required autocomplete="current-password" /><button type="button" class="absolute top-1/2 right-3 -translate-y-1/2 text-neutral-500" :aria-label="passwordRevealed ? 'Hide password' : 'Show password'" @click="passwordRevealed = !passwordRevealed"><EyeOff v-if="passwordRevealed" :size="18" /><Eye v-else :size="18" /></button></div><InputError class="mt-2" :message="form.errors.password" /></div>
                    <p class="mt-4 rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">Demo access: <strong>username</strong> / <strong>password</strong></p>
                    <div class="mt-5 flex items-center justify-between gap-4"><Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-gray-600 underline">Forgot password?</Link><EnButton type="submit" class="ml-auto" :loading="form.processing">Log in</EnButton></div>
                </form>
            </section>
        </div>
    </main>
</template>
