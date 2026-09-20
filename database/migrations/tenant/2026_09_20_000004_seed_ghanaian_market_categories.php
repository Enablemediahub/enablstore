<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
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

        DB::table('categories')->insertOrIgnore(
            collect($categories)->map(static fn (string $name): array => [
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
        );
    }

    public function down(): void
    {
        DB::table('categories')
            ->whereIn('slug', [
                'groceries-pantry',
                'beverages',
                'fresh-produce',
                'meat-fish-seafood',
                'local-traditional-foods',
                'household-cleaning',
                'personal-care-beauty',
                'baby-kids',
                'health-wellness',
                'home-kitchen',
                'electronics-accessories',
                'fashion-footwear',
                'stationery-office',
                'automotive',
            ])->delete();
    }
};