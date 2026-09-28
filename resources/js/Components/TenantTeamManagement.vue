<script setup lang="ts">
import EnButton from '@/Components/EnButton.vue';
import EnCard from '@/Components/EnCard.vue';
import { useForm } from '@inertiajs/vue3';
import { KeyRound, Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

type TeamUser = {
    id: number;
    name: string;
    username: string;
    email: string | null;
    role: 'admin' | 'cashier';
};

const props = defineProps<{
    users: TeamUser[];
    subscriberCode: string | null;
    enabled: boolean;
    status?: string | null;
}>();

const tenant = String(route().params.tenant);
const usernamePrefix = props.subscriberCode ? `${props.subscriberCode}-` : '';
const addingForm = useForm({
    name: '',
    username: '',
    username_suffix: '',
    email: '',
    password: '',
    role: 'cashier' as 'admin' | 'cashier',
    pos_pin: '',
});
const editingUser = ref<TeamUser | null>(null);
const editForm = useForm({
    name: '',
    username: '',
    username_suffix: '',
    email: '',
    role: 'cashier' as 'admin' | 'cashier',
});
const resettingUser = ref<TeamUser | null>(null);
const resetForm = useForm({ password: '', pin: '' });
const deleteForm = useForm({ team: '' });

const createUser = (): void => {
    addingForm.transform((data) => ({
        name: data.name,
        username: `${usernamePrefix}${data.username_suffix.trim().toLowerCase()}`,
        email: data.email,
        password: data.password,
        role: data.role,
        pos_pin: data.pos_pin,
    })).post(route('tenant.team.store', { tenant }), {
        preserveScroll: true,
        onSuccess: () => addingForm.reset(),
    });
};

const beginEdit = (user: TeamUser): void => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.username_suffix = usernamePrefix && user.username.startsWith(usernamePrefix)
        ? user.username.slice(usernamePrefix.length)
        : user.username;
    editForm.email = user.email ?? '';
    editForm.role = user.role;
    editForm.clearErrors();
};

const saveEdit = (): void => {
    if (!editingUser.value) return;

    editForm.transform((data) => ({
        name: data.name,
        username: `${usernamePrefix}${data.username_suffix.trim().toLowerCase()}`,
        email: data.email,
        role: data.role,
    })).patch(route('tenant.team.update', { tenant, user: editingUser.value.id }), {
        preserveScroll: true,
        onSuccess: () => { editingUser.value = null; },
    });
};

const beginReset = (user: TeamUser): void => {
    resettingUser.value = user;
    resetForm.reset();
    resetForm.clearErrors();
};

const saveReset = (): void => {
    if (!resettingUser.value) return;

    resetForm.transform((data) => resettingUser.value?.role === 'cashier'
        ? { pin: data.pin }
        : { password: data.password }).patch(route('tenant.team.reset-access', { tenant, user: resettingUser.value.id }), {
        preserveScroll: true,
        onSuccess: () => { resettingUser.value = null; resetForm.reset(); },
    });
};

const deleteUser = (user: TeamUser): void => {
    if (window.confirm(`Delete ${user.name}'s account? This cannot be undone.`)) {
        deleteForm.delete(route('tenant.team.destroy', { tenant, user: user.id }), { preserveScroll: true });
    }
};
</script>

<template>
    <EnCard>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Team accounts</h2>
                <p class="mt-1 text-sm text-neutral-500">Create and manage administrator and cashier access for this workspace.</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-bold" :class="enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-neutral-100 text-neutral-600'">{{ enabled ? 'Enabled' : 'Read-only' }}</span>
        </div>

        <p v-if="status" class="mt-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm font-medium text-green-800">{{ status }}</p>
        <p v-if="!enabled" class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-900">Team account changes are disabled by Enablstore support. You can still view the current team below.</p>

        <form v-if="enabled" class="mt-6 grid gap-4 rounded-md border border-neutral-200 p-4 sm:grid-cols-2 xl:grid-cols-3" @submit.prevent="createUser">
            <h3 class="font-semibold sm:col-span-2 xl:col-span-3">Add team member</h3>
            <label class="block text-sm font-medium text-neutral-700">Full name<input v-model="addingForm.name" required maxlength="120" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
            <label class="block text-sm font-medium text-neutral-700">Username<div class="mt-1.5 flex min-h-11 overflow-hidden rounded-md border border-neutral-300"><span v-if="usernamePrefix" class="inline-flex items-center border-r border-neutral-300 bg-neutral-50 px-3 font-mono text-sm text-neutral-700">{{ usernamePrefix }}</span><input v-model="addingForm.username_suffix" required class="min-w-0 flex-1 border-0 px-3 text-sm focus:outline-none focus:ring-0" /></div><span v-if="addingForm.errors.username" class="mt-1 block text-xs text-red-700">{{ addingForm.errors.username }}</span></label>
            <label class="block text-sm font-medium text-neutral-700">Role<select v-model="addingForm.role" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="cashier">Cashier</option><option value="admin">Administrator</option></select></label>
            <label class="block text-sm font-medium text-neutral-700">Email (optional)<input v-model="addingForm.email" type="email" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
            <label v-if="addingForm.role === 'admin'" class="block text-sm font-medium text-neutral-700">Login password<input v-model="addingForm.password" type="password" minlength="8" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span v-if="addingForm.errors.password" class="mt-1 block text-xs text-red-700">{{ addingForm.errors.password }}</span></label>
            <label v-else class="block text-sm font-medium text-neutral-700">POS PIN<input v-model="addingForm.pos_pin" type="password" inputmode="numeric" pattern="[0-9]{4,6}" minlength="4" maxlength="6" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span v-if="addingForm.errors.pos_pin" class="mt-1 block text-xs text-red-700">{{ addingForm.errors.pos_pin }}</span></label>
            <div class="flex items-end sm:col-span-2 xl:col-span-3"><EnButton type="submit" :loading="addingForm.processing"><Plus :size="16" aria-hidden="true" /> Add account</EnButton></div>
        </form>

        <div class="mt-6">
            <div v-if="users.length" class="divide-y divide-neutral-100">
                <article v-for="user in users" :key="user.id" class="flex flex-wrap items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="min-w-0"><p class="font-semibold text-neutral-900">{{ user.name }}</p><p class="mt-1 break-all font-mono text-xs text-neutral-500">{{ user.username }} · {{ user.email || 'No email' }}</p><span class="mt-2 inline-block rounded-full px-2.5 py-1 text-xs font-bold capitalize" :class="user.role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-violet-100 text-violet-800'">{{ user.role }}</span></div>
                    <div v-if="enabled" class="flex flex-wrap gap-2"><button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-neutral-300 px-3 text-xs font-semibold hover:border-neutral-500" @click="beginEdit(user)"><Pencil :size="14" aria-hidden="true" /> Edit</button><button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-blue-200 px-3 text-xs font-semibold text-blue-800 hover:bg-blue-50" @click="beginReset(user)"><KeyRound :size="14" aria-hidden="true" /> Reset access</button><button type="button" class="inline-flex min-h-9 items-center gap-1.5 rounded-md border border-red-200 px-3 text-xs font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50" :disabled="deleteForm.processing" @click="deleteUser(user)"><Trash2 :size="14" aria-hidden="true" /> Delete</button></div>
                </article>
            </div>
            <p v-else class="rounded-md bg-neutral-50 px-4 py-5 text-sm text-neutral-500">No team accounts have been created.</p>
            <p v-if="deleteForm.errors.team" class="mt-3 text-sm text-red-700">{{ deleteForm.errors.team }}</p>
        </div>
    </EnCard>

    <div v-if="editingUser" class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-950/50 p-4" @click.self="editingUser = null">
        <form class="w-full max-w-lg space-y-4 rounded-lg bg-white p-6 shadow-2xl" @submit.prevent="saveEdit">
            <div><h2 class="text-lg font-bold">Edit team member</h2><p class="mt-1 text-sm text-neutral-500">Update account details or role.</p></div>
            <label class="block text-sm font-medium text-neutral-700">Full name<input v-model="editForm.name" required maxlength="120" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
            <label class="block text-sm font-medium text-neutral-700">Username<div class="mt-1.5 flex min-h-11 overflow-hidden rounded-md border border-neutral-300"><span v-if="usernamePrefix" class="inline-flex items-center border-r border-neutral-300 bg-neutral-50 px-3 font-mono text-sm text-neutral-700">{{ usernamePrefix }}</span><input v-model="editForm.username_suffix" required class="min-w-0 flex-1 border-0 px-3 text-sm focus:outline-none focus:ring-0" /></div><span v-if="editForm.errors.username" class="mt-1 block text-xs text-red-700">{{ editForm.errors.username }}</span></label>
            <label class="block text-sm font-medium text-neutral-700">Role<select v-model="editForm.role" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="admin">Administrator</option><option value="cashier">Cashier</option></select><span v-if="editForm.errors.role" class="mt-1 block text-xs text-red-700">{{ editForm.errors.role }}</span></label>
            <label class="block text-sm font-medium text-neutral-700">Email (optional)<input v-model="editForm.email" type="email" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /></label>
            <div class="flex justify-end gap-2"><button type="button" class="min-h-10 rounded-md border border-neutral-300 px-4 text-sm font-semibold" @click="editingUser = null">Cancel</button><EnButton type="submit" :loading="editForm.processing">Save changes</EnButton></div>
        </form>
    </div>

    <div v-if="resettingUser" class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-950/50 p-4" @click.self="resettingUser = null">
        <form class="w-full max-w-md space-y-4 rounded-lg bg-white p-6 shadow-2xl" @submit.prevent="saveReset">
            <div><h2 class="text-lg font-bold">Reset {{ resettingUser.role === 'cashier' ? 'POS PIN' : 'password' }}</h2><p class="mt-1 text-sm text-neutral-500">For {{ resettingUser.name }}.</p></div>
            <label v-if="resettingUser.role === 'cashier'" class="block text-sm font-medium text-neutral-700">New POS PIN<input v-model="resetForm.pin" type="password" inputmode="numeric" pattern="[0-9]{4,6}" minlength="4" maxlength="6" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span v-if="resetForm.errors.pin" class="mt-1 block text-xs text-red-700">{{ resetForm.errors.pin }}</span></label>
            <label v-else class="block text-sm font-medium text-neutral-700">New password<input v-model="resetForm.password" type="password" minlength="8" required class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 px-3 text-sm" /><span v-if="resetForm.errors.password" class="mt-1 block text-xs text-red-700">{{ resetForm.errors.password }}</span></label>
            <div class="flex justify-end gap-2"><button type="button" class="min-h-10 rounded-md border border-neutral-300 px-4 text-sm font-semibold" @click="resettingUser = null">Cancel</button><EnButton type="submit" :loading="resetForm.processing">Reset access</EnButton></div>
        </form>
    </div>
</template>