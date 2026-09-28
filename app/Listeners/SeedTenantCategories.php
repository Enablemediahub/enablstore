<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stancl\Tenancy\Events\TenantCreated;

class SeedTenantCategories
{
    public function handle(TenantCreated $event): void
    {
        $categories = [
            'Groceries & Pantry',
            'Beverages',
            'Fresh Produce',
            'Meat, Fish & Seafood',
            'Local & Traditional Foods',
            'Household Cleaning',
            'Personal Care & Beauty',
            'Baby & Kids',
            'Health & Wellness',
            'Home & Kitchen',
            'Electronics & Accessories',
            'Fashion & Footwear',
            'Stationery & Office',
            'Automotive',
        ];
        $now = now();

        DB::table('categories')->insertOrIgnore(array_map(static fn (string $name): array => [
            'tenant_id' => $event->tenant->getTenantKey(),
            'name' => $name,
            'slug' => Str::slug($name),
            'created_at' => $now,
            'updated_at' => $now,
        ], $categories));
    }
}