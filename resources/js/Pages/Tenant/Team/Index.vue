<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

type TeamUser = { id: number; name: string; username: string; email: string; role: 'admin' | 'cashier' };
const props = defineProps<{ users: TeamUser[] }>();
const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
const form = useForm({ name: '', username: '', email: '', password: '', role: 'cashier' as 'admin' | 'cashier', pos_pin: '' });
const submit = (): void => form.post(route('tenant.team.store', { tenant }), { onSuccess: () => form.reset() });
const changeRole = (user: TeamUser): void => form.transform(() => ({ role: user.role === 'admin' ? 'cashier' : 'admin' })).patch(route('tenant.team.update', { tenant, user: user.id }));
</script>
<template>
    <Head title="Team" />
    <main class="min-h-screen bg-neutral-50"><div class="mx-auto flex min-h-screen max-w-[1600px]"><AdminSidePanel :tenant="tenant" current="team" :mobile-open="mobilePanelOpen" @close="mobilePanelOpen = false" /><section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10"><div class="mx-auto max-w-6xl space-y-8"><button type="button" class="mb-5 inline-flex rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm lg:hidden" @click="mobilePanelOpen = true">Open admin navigation</button><header><p class="text-sm font-semibold text-red-700">Operations</p><h1 class="mt-1 text-3xl font-bold">Team & cashier access</h1><p class="mt-2 text-sm text-neutral-500">Create separate logins for each sales person. Cashiers can use POS only.</p></header><div class="grid gap-6 lg:grid-cols-[360px_1fr]"><EnCard><h2 class="text-lg font-semibold">Add team member</h2><form class="mt-5 space-y-3" @submit.prevent="submit"><input v-model="form.name" required placeholder="Full name" class="min-h-11 w-full rounded-md border px-3 text-sm" /><input v-model="form.username" required placeholder="Username" class="min-h-11 w-full rounded-md border px-3 text-sm" /><input v-model="form.email" required type="email" placeholder="Email" class="min-h-11 w-full rounded-md border px-3 text-sm" /><input v-model="form.password" required type="password" placeholder="Login password" class="min-h-11 w-full rounded-md border px-3 text-sm" /><select v-model="form.role" class="min-h-11 w-full rounded-md border px-3 text-sm"><option value="cashier">Cashier</option><option value="admin">Admin</option></select><input v-if="form.role === 'cashier'" v-model="form.pos_pin" required inputmode="numeric" pattern="[0-9]{4,6}" placeholder="POS PIN (4–6 digits)" class="min-h-11 w-full rounded-md border px-3 text-sm" /><EnButton type="submit" :loading="form.processing">Create login</EnButton></form></EnCard><EnCard><h2 class="text-lg font-semibold">Shop team</h2><div class="mt-4 divide-y divide-neutral-100"><div v-for="user in props.users" :key="user.id" class="flex flex-wrap items-center justify-between gap-3 py-4"><div><p class="font-semibold">{{ user.name }}</p><p class="text-sm text-neutral-500">{{ user.username }} · {{ user.email }}</p><p class="mt-1 text-xs font-bold uppercase text-red-700">{{ user.role }}</p></div><button type="button" class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700" @click="changeRole(user)">{{ user.role === 'admin' ? 'Make cashier' : 'Make admin' }}</button></div></div></EnCard></div></div></section></div></main>
</template>
