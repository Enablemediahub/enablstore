<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Core tools for a growing retail shop.',
                'price_minor' => 9900,
                'currency' => 'GHS',
                'billing_interval' => 'monthly',
                'features' => [
                    'products',
                    'inventory',
                    'pos',
                    'online_store',
                ],
                'is_active' => true,
            ],
        );
    }
}