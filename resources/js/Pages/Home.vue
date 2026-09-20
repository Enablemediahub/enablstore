<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ username: 'username', password: 'password', remember: true });

const submit = (): void => {
    form.post(route('login'), { onFinish: () => form.reset('password') });
};
</script>

<template>
    <Head title="Sign in to Enablstore" />
    <main class="min-h-screen bg-[#f4f4f2] text-[#171717]">
        <header class="bg-[#171717] px-5 py-4 text-white sm:px-10">
            <div class="mx-auto flex max-w-6xl items-center justify-between">
                <img src="/images/Enablstore-cropped.png" alt="Enablstore" class="h-14 w-auto max-w-[80px] object-contain" />
                <Link href="/super-admin/login" class="text-sm text-neutral-300 hover:text-white">Superadmin</Link>
            </div>
        </header>
        <section class="mx-auto grid min-h-[calc(100vh-84px)] max-w-6xl items-center gap-10 px-5 py-12 sm:px-10 lg:grid-cols-[1fr_420px]">
            <div>
                <p class="text-sm font-bold tracking-[0.2em] text-[#e21b23] uppercase">Your retail workspace</p>
                <h1 class="mt-4 max-w-xl text-4xl font-black leading-tight sm:text-6xl">One login. Every way to sell.</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-[#555555]">Choose the tools your subscription gives you access to, then get straight to work.</p>
                <div class="mt-8 flex gap-3 text-sm text-[#555555]"><span class="rounded-full bg-white px-4 py-2 shadow-sm">Online Store</span><span class="rounded-full bg-white px-4 py-2 shadow-sm">POS</span></div>
            </div>
            <EnCard class="border-neutral-200 bg-white p-7 shadow-xl sm:p-9">
                <p class="text-xs font-bold tracking-widest text-[#e21b23] uppercase">Welcome back</p>
                <h2 class="mt-2 text-2xl font-black">Sign in to continue</h2>
                <p class="mt-2 text-sm text-[#555555]">Use your workspace username and password.</p>
                <form class="mt-7" @submit.prevent="submit">
                    <div>
                        <InputLabel for="username" value="Username" />
                        <TextInput id="username" v-model="form.username" type="text" class="mt-1 block w-full" required autofocus autocomplete="username" />
                        <InputError class="mt-2" :message="form.errors.username" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="password" value="Password" />
                        <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>
                    <p class="mt-4 rounded-md bg-red-50 px-3 py-2 text-xs text-red-700">Demo access: <strong>username</strong> / <strong>password</strong></p>
                    <EnButton type="submit" class="mt-5 w-full" :loading="form.processing">Enter workspace</EnButton>
                </form>
            </EnCard>
        </section>
    </main>
</template>
