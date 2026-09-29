<x-filament-panels::page>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Subscription revenue</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $metrics['revenue'] }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Paid payments</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['paid_count']) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending payments</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['pending_count']) }}</p>
        </x-filament::section>
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">Renewals in 7 days</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['upcoming_renewals']) }}</p>
        </x-filament::section>
    </div>
    {{ $this->table }}
</x-filament-panels::page>