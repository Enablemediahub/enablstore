<script setup lang="ts">
import EnCard from '@/Components/EnCard.vue';
import EnInput from '@/Components/EnInput.vue';
import SuperAdminSidePanel from '@/Components/SuperAdminSidePanel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, KeyRound, Pencil, Store, Trash2, Users } from '@lucide/vue';
import { ref } from 'vue';

type Tenant = {
    id: string;
    subscriber_code: string | null;
    name: string;
    slug: string;
    email: string | null;
    phone: string | null;
    status: 'active' | 'suspended';
    created_at: string;
};

type Subscription = {
    id: number;
    plan_id: number;
    plan_name: string | null;
    status: string;
    renews_at: string | null;
} | null;

type StorefrontSettings = {
    catalogue_mode: 'shared' | 'separate_online';
    storefront_store_name: string;
    storefront_delivery_message: string;
    storefront_hero_delivery_message: string;
    storefront_customer_service_phone: string;
    storefront_customer_service_email: string;
};

type TeamMember = { id: number; name: string; username: string; email: string | null; role: 'admin' | 'cashier'; created_at: string | null };

const props = defineProps<{
    tenant: Tenant;
    subscription: Subscription;
    plans: Array<{ id: number; name: string }>;
    storefrontSettings: StorefrontSettings;
    team: TeamMember[];
    status?: string | null;
}>();

const form = useForm({
    name: props.tenant.name,
    email: props.tenant.email ?? '',
    phone: props.tenant.phone ?? '',
    status: props.tenant.status,
    subscription_plan_id: props.subscription?.plan_id ?? '',
    subscription_status: props.subscription?.status ?? '',
    ...props.storefrontSettings,
});

const submit = (): void => form.patch(route('super-admin.tenants.update', { tenant: props.tenant.id }));

const editingMember = ref<TeamMember | null>(null);
const resettingMember = ref<TeamMember | null>(null);
const teamActions = useForm({});
const teamEdit = useForm<{ name: string; username: string; email: string; role: 'admin' | 'cashier' }>({ name: '', username: '', email: '', role: 'cashier' });
const resetAccess = useForm({ password: '', pin: '' });
const beginEdit = (member: TeamMember): void => {
    editingMember.value = member;
    teamEdit.name = member.name;
    teamEdit.username = member.username;
    teamEdit.email = member.email ?? '';
    teamEdit.role = member.role;
};
const saveMember = (): void => {
    if (editingMember.value) teamEdit.patch(route('super-admin.users.update', { user: editingMember.value.id }), { onSuccess: () => { editingMember.value = null; } });
};
const beginReset = (member: TeamMember): void => { resettingMember.value = member; resetAccess.reset(); };
const saveReset = (): void => {
    if (resettingMember.value) resetAccess.patch(route('super-admin.users.reset-access', { user: resettingMember.value.id }), { onSuccess: () => { resettingMember.value = null; } });
};
const deleteMember = (member: TeamMember): void => {
    if (window.confirm(`Delete ${member.name}'s account? This cannot be undone.`)) teamActions.delete(route('super-admin.users.destroy', { user: member.id }), { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Manage ${tenant.name}`" />
    <main class="flex min-h-screen bg-neutral-50 text-neutral-900">
        <SuperAdminSidePanel current="tenants" />
        <section class="min-w-0 flex-1 px-5 py-8 sm:px-10">
            <div class="mx-auto max-w-5xl">
                <Link :href="route('super-admin.dashboard')" class="inline-flex items-center gap-2 text-sm font-semibold text-neutral-600 hover:text-[#e21b23]">
                    <ArrowLeft :size="16" aria-hidden="true" /> All enrolled tenants
                </Link>
                <header class="mt-5 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[#e21b23]">Tenant management</p>
                        <h1 class="mt-1 text-3xl font-black">{{ tenant.name }}</h1>
                        <p class="mt-2 text-sm text-neutral-500">Configure this subscribed tenant's account, subscription, and Online Store presentation.</p>
                    </div>
                    <a :href="route('tenant.home', { tenant: tenant.id })" target="_blank" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-semibold hover:border-[#e21b23] hover:text-[#e21b23]">
                        <ExternalLink :size="16" aria-hidden="true" /> View Online Store
                    </a>
                </header>

                <p v-if="status" class="mt-5 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ status }}</p>

                <form class="mt-8 space-y-7" @submit.prevent="submit">
                    <EnCard>
                        <div class="flex items-start gap-3"><Store :size="22" class="mt-0.5 text-[#e21b23]" aria-hidden="true" /><div><h2 class="text-lg font-bold">Tenant account</h2><p class="mt-1 text-sm text-neutral-500">The account details used to identify this subscribed workspace.</p></div></div>
                        <div class="mt-6 space-y-4">
                            <EnInput id="tenant-name" v-model="form.name" label="Tenant / business name" required />
                            <EnInput id="tenant-email" v-model="form.email" type="email" label="Contact email" />
                            <EnInput id="tenant-phone" v-model="form.phone" label="Contact phone" />
                            <label class="block text-sm font-medium text-neutral-700">Workspace status<select v-model="form.status" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="active">Active</option><option value="suspended">Suspended</option></select></label>
                        </div>
                        <p class="mt-4 text-xs text-neutral-500">Subscriber code: <strong class="font-mono text-neutral-700">{{ tenant.subscriber_code }}</strong> · Tenant ID: {{ tenant.id }} · Store URL: /onlinestore/{{ tenant.slug }}</p>
                    </EnCard>

                    <EnCard>
                        <div class="flex items-start gap-3"><Users :size="22" class="mt-0.5 text-[#e21b23]" aria-hidden="true" /><div><h2 class="text-lg font-bold">Subscriber team</h2><p class="mt-1 text-sm text-neutral-500">The first administrator is the main administrator created when this subscriber was enrolled. Other administrators and cashiers are enrolled by that main administrator.</p></div></div>
                        <div v-if="team.length" class="mt-6 overflow-x-auto"><table class="w-full min-w-[760px] text-left text-sm"><thead class="border-b border-neutral-200 text-xs font-semibold tracking-wide text-neutral-500 uppercase"><tr><th class="px-3 py-3">Team member</th><th class="px-3 py-3">Username</th><th class="px-3 py-3">Role</th><th class="px-3 py-3">Contact</th><th class="px-3 py-3">Added</th><th class="px-3 py-3 text-right">Actions</th></tr></thead><tbody><tr v-for="(member, index) in team" :key="member.id" class="border-b border-neutral-100 last:border-0"><td class="px-3 py-4 font-semibold">{{ member.name }}</td><td class="px-3 py-4 font-mono text-xs">{{ member.username }}</td><td class="px-3 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="member.role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-violet-100 text-violet-800'">{{ index === 0 && member.role === 'admin' ? 'Main admin' : member.role }}</span></td><td class="px-3 py-4 text-neutral-600">{{ member.email || '—' }}</td><td class="px-3 py-4 text-neutral-600">{{ member.created_at || '—' }}</td><td class="px-3 py-4"><div class="flex justify-end gap-2"><button type="button" class="inline-flex min-h-9 items-center gap-1 rounded-md border border-neutral-300 px-3 text-xs font-bold hover:border-[#e21b23]" @click="beginEdit(member)"><Pencil :size="14" /> Edit</button><button type="button" class="inline-flex min-h-9 items-center gap-1 rounded-md border border-neutral-300 px-3 text-xs font-bold hover:border-blue-500 hover:text-blue-700" @click="beginReset(member)"><KeyRound :size="14" /> Reset</button><button type="button" class="inline-flex min-h-9 items-center gap-1 rounded-md border border-red-200 px-3 text-xs font-bold text-red-700 hover:bg-red-50" :disabled="teamActions.processing" @click="deleteMember(member)"><Trash2 :size="14" /> Delete</button></div></td></tr></tbody></table></div>
                        <p v-else class="mt-5 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">No team members have been added to this subscriber.</p>
                    </EnCard>

                    <EnCard>
                        <h2 class="text-lg font-bold">Subscription</h2>
                        <p class="mt-1 text-sm text-neutral-500">Manage the current plan and access state for this tenant.</p>
                        <div v-if="subscription" class="mt-6 space-y-4">
                            <label class="block text-sm font-medium text-neutral-700">Plan<select v-model="form.subscription_plan_id" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option></select></label>
                            <label class="block text-sm font-medium text-neutral-700">Subscription status<select v-model="form.subscription_status" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="trialing">Trialing</option><option value="active">Active</option><option value="past_due">Past due</option><option value="disabled">Disabled</option><option value="cancelled">Cancelled</option></select></label>
                        </div>
                        <p v-else class="mt-5 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800">This tenant does not yet have a subscription.</p>
                    </EnCard>

                    <EnCard>
                        <h2 class="text-lg font-bold">Online Store settings</h2>
                        <p class="mt-1 text-sm text-neutral-500">Settings here only affect {{ tenant.name }}'s storefront.</p>
                        <div class="mt-6 space-y-4">
                            <EnInput id="store-display-name" v-model="form.storefront_store_name" label="Store display name" :placeholder="tenant.name" />
                            <label class="block text-sm font-medium text-neutral-700">Catalogue mode<select v-model="form.catalogue_mode" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="shared">Shared POS products</option><option value="separate_online">Separate online products</option></select></label>
                            <EnInput id="delivery-message" v-model="form.storefront_delivery_message" label="Navigation delivery message" />
                            <EnInput id="hero-delivery-message" v-model="form.storefront_hero_delivery_message" label="Hero delivery message" />
                            <EnInput id="support-phone" v-model="form.storefront_customer_service_phone" label="Customer service phone" />
                            <EnInput id="support-email" v-model="form.storefront_customer_service_email" type="email" label="Customer service email" />
                        </div>
                    </EnCard>

                    <div class="flex justify-end"><button type="submit" class="min-h-11 rounded-md bg-[#e21b23] px-5 py-2 text-sm font-bold text-white hover:bg-[#b9151b] disabled:opacity-60" :disabled="form.processing">Save tenant settings</button></div>
                </form>
                <div v-if="editingMember" class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-950/50 p-4" @click.self="editingMember = null"><form class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl" @submit.prevent="saveMember"><div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-bold">Edit team member</h2><p class="mt-1 text-sm text-neutral-500">Keep the subscriber-code prefix in the username. If the role changes, use Reset access afterward to set the appropriate credential.</p></div><button type="button" class="text-sm font-semibold text-neutral-500" @click="editingMember = null">Cancel</button></div><div class="mt-5 space-y-4"><EnInput id="team-name" v-model="teamEdit.name" label="Full name" required /><EnInput id="team-username" v-model="teamEdit.username" label="Username" required /><label class="block text-sm font-medium text-neutral-700">Role<select v-model="teamEdit.role" class="mt-1.5 min-h-11 w-full rounded-md border border-neutral-300 bg-white px-3 text-sm"><option value="admin">Administrator — password login</option><option value="cashier">Cashier — POS PIN login</option></select></label><EnInput id="team-email" v-model="teamEdit.email" type="email" label="Email (optional)" /></div><p v-if="teamEdit.errors.username || teamEdit.errors.email || teamEdit.errors.role" class="mt-4 text-sm text-red-700">{{ teamEdit.errors.username || teamEdit.errors.email || teamEdit.errors.role }}</p><div class="mt-6 flex justify-end gap-3"><button type="button" class="rounded-md border border-neutral-300 px-4 py-2 text-sm font-bold" @click="editingMember = null">Cancel</button><button type="submit" class="rounded-md bg-[#171717] px-4 py-2 text-sm font-bold text-white" :disabled="teamEdit.processing">Save member</button></div></form></div>
                <div v-if="resettingMember" class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-950/50 p-4" @click.self="resettingMember = null"><form class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl" @submit.prevent="saveReset"><div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-bold">Reset access</h2><p class="mt-1 text-sm text-neutral-500">{{ resettingMember.role === 'cashier' ? `Set a new POS PIN for ${resettingMember.name}.` : `Set a new login password for ${resettingMember.name}.` }}</p></div><button type="button" class="text-sm font-semibold text-neutral-500" @click="resettingMember = null">Cancel</button></div><div class="mt-5"><EnInput v-if="resettingMember.role === 'cashier'" id="cashier-pin" v-model="resetAccess.pin" type="password" inputmode="numeric" label="New POS PIN" minlength="4" maxlength="6" required /><EnInput v-else id="admin-password" v-model="resetAccess.password" type="password" label="New login password" minlength="8" required /></div><p v-if="resetAccess.errors.pin || resetAccess.errors.password" class="mt-4 text-sm text-red-700">{{ resetAccess.errors.pin || resetAccess.errors.password }}</p><div class="mt-6 flex justify-end gap-3"><button type="button" class="rounded-md border border-neutral-300 px-4 py-2 text-sm font-bold" @click="resettingMember = null">Cancel</button><button type="submit" class="rounded-md bg-[#e21b23] px-4 py-2 text-sm font-bold text-white" :disabled="resetAccess.processing">Reset access</button></div></form></div>
            </div>
        </section>
    </main>
</template>
