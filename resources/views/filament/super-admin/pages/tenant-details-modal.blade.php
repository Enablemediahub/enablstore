<dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscriber code</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $tenant->subscriber_code ?: 'Not assigned' }}</dd>
    </div>
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Workspace</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $tenant->name }}</dd>
    </div>
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $tenant->email ?: 'Not provided' }}</dd>
    </div>
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $tenant->phone ?: 'Not provided' }}</dd>
    </div>
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Workspace status</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ str($tenant->status)->replace('_', ' ')->title() }}</dd>
    </div>
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $subscription?->plan?->name ?? 'No plan' }}</dd>
    </div>
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ str($subscription?->status ?? 'none')->replace('_', ' ')->title() }}</dd>
    </div>
    <div>
        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Renews</dt>
        <dd class="mt-1 text-sm text-gray-950 dark:text-white">{{ $subscription?->renews_at?->toDateString() ?? 'Not scheduled' }}</dd>
    </div>
</dl>