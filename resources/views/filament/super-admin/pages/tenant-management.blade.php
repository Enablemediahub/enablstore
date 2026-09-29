<x-filament-panels::page>
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Subscriber code: {{ $tenant->subscriber_code }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Store URL: /onlinestore/{{ $tenant->slug }}</p>
        </div>
        <x-filament::link :href="\App\Filament\SuperAdmin\Pages\TenantDirectory::getUrl()" icon="heroicon-m-arrow-left">
            Subscribers
        </x-filament::link>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}
        <div class="flex justify-end">
            <x-filament::button type="submit">Save tenant settings</x-filament::button>
        </div>
    </form>

    <x-filament::section class="mt-6">
        <x-slot name="heading">Subscriber team</x-slot>
        <x-slot name="description">Manage administrators and cashiers, reset passwords or POS PINs, and protect the last administrator account.</x-slot>
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>