<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import SuperAdminSidePanel from '@/Components/SuperAdminSidePanel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

type Admin = { id: number; name: string; email: string };
const props = defineProps<{ admins: Admin[] }>();
const form = useForm({ name: '', email: '', password: '' });
const editingId = ref<number | null>(null);
const editingForm = useForm({ name: '', email: '', password: '' });
const submit = (): void => form.post(route('super-admin.accounts.store'), { onSuccess: () => form.reset() });
const editAdmin = (admin: Admin): void => { editingId.value = admin.id; editingForm.name = admin.name; editingForm.email = admin.email; editingForm.password = ''; };
const updateAdmin = (): void => { if (editingId.value !== null) editingForm.patch(route('super-admin.accounts.update', { superAdmin: editingId.value }), { onSuccess: () => { editingId.value = null; } }); };
</script>
<template>
    <Head title="Superadmins" />
    <main class="flex min-h-screen bg-neutral-50 text-neutral-900"><SuperAdminSidePanel current="accounts" /><section class="min-w-0 flex-1 px-5 py-8 sm:px-10"><header><p class="text-sm font-semibold text-[#e21b23]">Access control</p><h1 class="mt-1 text-3xl font-black">Superadmins</h1><p class="mt-2 text-neutral-500">Add trusted administrators who can manage branding, workspaces, and platform access.</p></header><div class="mt-8 grid gap-8 xl:grid-cols-[400px_1fr]"><EnCard><h2 class="text-lg font-bold">Add superadmin</h2><form class="mt-5 space-y-4" @submit.prevent="submit"><EnInput id="admin-name" v-model="form.name" label="Full name" required /><EnInput id="admin-email" v-model="form.email" type="email" label="Email" required /><EnInput id="admin-password" v-model="form.password" type="password" label="Password" required /><button type="submit" class="w-full rounded-md bg-[#e21b23] px-4 py-3 text-sm font-bold text-white disabled:opacity-60" :disabled="form.processing">Add superadmin</button></form></EnCard><EnCard><h2 class="text-lg font-bold">Current superadmins</h2><div class="mt-5 space-y-3"><div v-for="admin in props.admins" :key="admin.id" class="flex items-center justify-between gap-4 border-b border-neutral-100 py-3"><div><p class="font-semibold">{{ admin.name }}</p><p class="text-sm text-neutral-500">{{ admin.email }}</p></div><button type="button" class="text-sm font-bold text-[#e21b23]" @click="editAdmin(admin)">Edit</button></div></div><form v-if="editingId !== null" class="mt-6 border-t border-neutral-200 pt-5" @submit.prevent="updateAdmin"><h3 class="font-bold">Edit superadmin</h3><div class="mt-4 grid gap-4 sm:grid-cols-3"><EnInput id="edit-admin-name" v-model="editingForm.name" label="Full name" required /><EnInput id="edit-admin-email" v-model="editingForm.email" type="email" label="Email" required /><EnInput id="edit-admin-password" v-model="editingForm.password" type="password" label="New password (optional)" /></div><button type="submit" class="mt-4 rounded-md bg-[#171717] px-4 py-2 text-sm font-bold text-white" :disabled="editingForm.processing">Save changes</button></form></EnCard></div></section></main>
+</template>
