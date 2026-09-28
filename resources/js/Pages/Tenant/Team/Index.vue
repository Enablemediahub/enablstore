<script setup lang="ts">
import AdminSidePanel from '@/Components/AdminSidePanel.vue';
import TenantTeamManagement from '@/Components/TenantTeamManagement.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

type TeamUser = { id: number; name: string; username: string; email: string | null; role: 'admin' | 'cashier' };
const props = defineProps<{ users: TeamUser[]; subscriberCode: string | null; teamManagementEnabled: boolean; status?: string | null }>();
const tenant = String(route().params.tenant);
const mobilePanelOpen = ref(false);
</script>
<template>
    <Head title="Team" />
    <main class="min-h-screen bg-neutral-50">
        <div class="mx-auto flex min-h-screen max-w-[1600px]">
            <AdminSidePanel
                :tenant="tenant"
                current="team"
                :mobile-open="mobilePanelOpen"
                @close="mobilePanelOpen = false"
            />
            <section class="min-w-0 flex-1 px-4 py-6 sm:px-8 sm:py-10">
                <div class="mx-auto max-w-6xl space-y-8">
                    <button
                        type="button"
                        class="mb-5 inline-flex rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm lg:hidden"
                        @click="mobilePanelOpen = true"
                    >
                        Open admin navigation
                    </button>
                    <header>
                        <p class="text-sm font-semibold text-red-700">
                            Operations
                        </p>
                        <h1 class="mt-1 text-3xl font-bold">
                            Team & cashier access
                        </h1>
                        <p class="mt-2 text-sm text-neutral-500">
                            Create separate logins for each sales person.
                            Cashiers can use POS only.
                        </p>
                    </header>
                    <TenantTeamManagement :users="props.users" :subscriber-code="props.subscriberCode" :enabled="props.teamManagementEnabled" :status="props.status" />
                </div>
            </section>
        </div>
    </main>
</template>
