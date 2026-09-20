<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    business_name: '',
    slug: '',
    email: '',
    phone: '',
    owner_name: '',
});

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
                    <EnButton
                        type="submit"
                        :loading="form.processing"
                        class="w-full"
                    >
                        Create store
                    </EnButton>
                </form>
            </EnCard>
        </div>
    </main>
</template>
