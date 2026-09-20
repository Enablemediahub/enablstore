<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = (): void => {
    form.post(route('super-admin.login.store'));
};
</script>

<template>
    <Head title="Super admin login" />
    <main
        class="flex min-h-screen items-center justify-center bg-neutral-900 px-4 py-12"
    >
        <EnCard class="w-full max-w-md">
            <p class="text-primary-700 text-sm font-semibold">
                Enablstore control panel
            </p>
            <h1 class="mt-2 text-2xl font-bold text-neutral-900">
                Super admin login
            </h1>
            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <EnInput
                    id="admin-username"
                    v-model="form.username"
                    type="text"
                    label="Username"
                    :error="form.errors.username"
                    required
                />
                <EnInput
                    id="admin-password"
                    v-model="form.password"
                    type="password"
                    label="Password"
                    :error="form.errors.password"
                    required
                />
                <EnButton
                    type="submit"
                    :loading="form.processing"
                    class="w-full"
                >
                    Sign in
                </EnButton>
                <Link
                    href="/login"
                    class="block w-full rounded-md border border-neutral-300 px-4 py-2 text-center text-sm font-semibold text-neutral-700 transition hover:bg-neutral-100"
                >
                    Admin login
                </Link>
            </form>
        </EnCard>
    </main>
</template>
