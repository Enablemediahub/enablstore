<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import SuperAdminSidePanel from '@/Components/SuperAdminSidePanel.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { ref } from 'vue';

type User = {
    id: number;
    name: string;
    username: string;
    email: string | null;
    role: 'admin' | 'cashier';
    tenant?: { name: string } | null;
};
const props = defineProps<{
    users: { data: User[] };
    plans: Array<{ id: number; name: string }>;
}>();
const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    business_name: '',
    plan_id: props.plans[0]?.id ?? '',
});
const editingId = ref<number | null>(null);
const editingForm = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    role: 'admin' as 'admin' | 'cashier',
});
const submit = (): void =>
    form.post(route('super-admin.users.store'), {
        onSuccess: () =>
            form.reset(
                'name',
                'username',
                'email',
                'password',
                'business_name',
            ),
    });
const editUser = (user: User): void => {
    editingId.value = user.id;
    editingForm.name = user.name;
    editingForm.username = user.username;
    editingForm.email = user.email ?? '';
    editingForm.password = '';
    editingForm.role = user.role;
};
const updateUser = (): void => {
    if (editingId.value !== null)
        editingForm.patch(
            route('super-admin.users.update', { user: editingId.value }),
            {
                onSuccess: () => {
                    editingId.value = null;
                },
            },
        );
};
const deleteUser = (user: User): void => {
    if (
        window.confirm(
            `Delete ${user.name}'s login? This keeps the tenant and subscription, but permanently removes this user account.`,
        )
    ) {
        form.delete(route('super-admin.users.destroy', { user: user.id }), {
            preserveScroll: true,
        });
    }
};
</script>
<template>
    <Head title="Add subscriber" />
    <main class="flex min-h-screen bg-neutral-50 text-neutral-900">
        <SuperAdminSidePanel current="tenants" />
        <section class="min-w-0 flex-1 px-5 py-8 sm:px-10">
            <header>
                <p class="text-sm font-semibold text-[#e21b23]">
                    Subscriber onboarding
                </p>
                <h1 class="mt-1 text-3xl font-black">Add subscriber</h1>
                <p class="mt-2 text-neutral-500">
                    Create a subscriber business, its first administrator login,
                    and its subscription in one step.
                </p>
            </header>
            <div class="mt-8 grid gap-8 xl:grid-cols-[400px_1fr]">
                <EnCard
                    ><h2 class="text-lg font-bold">New subscriber</h2>
                    <form class="mt-5 space-y-4" @submit.prevent="submit">
                        <EnInput
                            id="user-name"
                            v-model="form.name"
                            label="Full name"
                            required
                        /><EnInput
                            id="user-username"
                            v-model="form.username"
                            label="Username"
                            required
                        /><EnInput
                            id="user-email"
                            v-model="form.email"
                            type="email"
                            label="Email"
                            required
                        /><EnInput
                            id="user-password"
                            v-model="form.password"
                            type="password"
                            label="Password"
                            required
                        /><EnInput
                            id="business-name"
                            v-model="form.business_name"
                            label="Business name"
                            required
                        /><label
                            class="block text-sm font-medium text-neutral-700"
                            >Subscription plan<select
                                v-model="form.plan_id"
                                class="mt-1 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"
                            >
                                <option
                                    v-for="plan in plans"
                                    :key="plan.id"
                                    :value="plan.id"
                                >
                                    {{ plan.name }}
                                </option>
                            </select></label
                        ><button
                            type="submit"
                            class="w-full rounded-md bg-[#e21b23] px-4 py-3 text-sm font-bold text-white disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            Create subscriber
                        </button>
                    </form></EnCard
                ><EnCard
                    ><h2 class="text-lg font-bold">Existing users</h2>
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="border-b border-neutral-200 text-xs text-neutral-500 uppercase"
                            >
                                <tr>
                                    <th class="px-3 py-3">Name</th>
                                    <th class="px-3 py-3">Username</th>
                                    <th class="px-3 py-3">Workspace</th>
                                    <th class="px-3 py-3">Email</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="border-b border-neutral-100"
                                >
                                    <td class="px-3 py-4 font-semibold">
                                        {{ user.name }}
                                    </td>
                                    <td class="px-3 py-4">
                                        {{ user.username }}
                                    </td>
                                    <td class="px-3 py-4">
                                        {{ user.tenant?.name ?? 'Unassigned' }}
                                    </td>
                                    <td class="px-3 py-4">{{ user.email }}</td>
                                    <td class="px-3 py-4 text-right">
                                        <div
                                            class="inline-flex items-center gap-3"
                                        >
                                            <button
                                                type="button"
                                                class="font-semibold text-[#e21b23]"
                                                @click="editUser(user)"
                                            >
                                                Edit</button
                                            ><button
                                                type="button"
                                                class="inline-flex items-center gap-1 font-semibold text-red-700 hover:text-red-900"
                                                :disabled="form.processing"
                                                @click="deleteUser(user)"
                                            >
                                                <Trash2
                                                    :size="15"
                                                    aria-hidden="true"
                                                />
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="editingId !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-950/50 p-4" @click.self="editingId = null">
                    <form class="max-h-[90dvh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-2xl" @submit.prevent="updateUser">
                        <div class="flex items-start justify-between gap-4"><div><h3 class="font-bold">Edit login details</h3><p class="mt-1 text-sm text-neutral-500">Use the full prefixed username. A password is only required when you want to change it.</p></div><button type="button" class="text-sm font-semibold text-neutral-500 hover:text-neutral-900" @click="editingId = null">Cancel</button></div>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <EnInput
                                id="edit-user-name"
                                v-model="editingForm.name"
                                label="Full name"
                                required
                            /><EnInput
                                id="edit-user-username"
                                v-model="editingForm.username"
                                label="Username"
                                required
                            /><EnInput
                                id="edit-user-email"
                                v-model="editingForm.email"
                                type="email"
                                label="Email (optional)"
                            /><EnInput
                                id="edit-user-password"
                                v-model="editingForm.password"
                                type="password"
                                label="New password (optional)"
                            />
                        </div>
                        <p v-if="editingForm.errors.username || editingForm.errors.email" class="mt-4 text-sm text-red-700">{{ editingForm.errors.username || editingForm.errors.email }}</p>
                        <div class="mt-5 flex justify-end gap-3"><button type="button" class="rounded-md border border-neutral-300 px-4 py-2 text-sm font-bold text-neutral-700" @click="editingId = null">Cancel</button><button type="submit" class="rounded-md bg-[#171717] px-4 py-2 text-sm font-bold text-white" :disabled="editingForm.processing">Save changes</button></div>
                    </form></div></EnCard
                >
            </div>
        </section>
    </main>
</template>
