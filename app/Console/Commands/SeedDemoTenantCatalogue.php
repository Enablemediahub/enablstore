<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedDemoTenantCatalogue extends Command
{
    protected $signature = 'demo:seed-catalogue {--tenant=demo : Tenant name or ID to populate}';

    protected $description = 'Populate a demo tenant with Ghana-market products for Online Store and POS.';

    public function handle(): int
    {
        $target = trim((string) $this->option('tenant'));
        $tenant = Tenant::query()
            ->where('id', $target)
            ->orWhereRaw('LOWER(name) = ?', [Str::lower($target)])
            ->first();

        if ($tenant === null) {
            $this->error("No tenant matched '{$target}'. Available tenants: ".Tenant::query()->pluck('name', 'id')->map(fn ($name, $id) => "{$name} ({$id})")->join(', '));

            return self::FAILURE;
        }

        $count = $tenant->run(function (): int {
            $categories = Category::query()->pluck('id', 'slug');

            TenantSetting::query()->updateOrCreate(
                ['key' => 'catalogue_mode'],
                ['value' => 'shared'],
            );

            foreach ($this->products() as $item) {
                $categoryId = $categories[$item['category']] ?? null;
                $image = str_starts_with($item['image'], 'images/')
                    ? $this->seedPhotoForCategory($item['category'])
                    : $item['image'];

                $product = Product::query()->updateOrCreate(
                    ['sku' => $item['sku']],
                    [
                        'category_id' => $categoryId,
                        'name' => $item['name'],
                        'slug' => Str::slug($item['name']),
                        'barcode' => $item['barcode'],
                        'price_minor' => $item['price'] * 100,
                        'compare_at_price_minor' => $item['deal'] ? ($item['price'] + 3) * 100 : null,
                        'cost_minor' => (int) round($item['price'] * 70),
                        'purchase_unit' => 'unit',
                        'units_per_purchase' => 1,
                        'currency' => 'GHS',
                        'description' => $item['description'],
                        'image_path' => $image,
                        'image_gallery' => [$image],
                        'is_active' => true,
                        'available_in_pos' => true,
                        'available_online' => true,
                        'is_online_deal' => $item['deal'],
                    ],
                );

                InventoryStock::query()->updateOrCreate(
                    ['product_id' => $product->id],
                    ['quantity' => $item['stock'], 'low_stock_threshold' => 8],
                );
            }

            return count($this->products());
        });

        $this->info("Seeded {$count} Ghana-market products for {$tenant->name}. They are enabled for both Online Store and POS.");

        return self::SUCCESS;
    }

    /** @return array<int, array{name: string, sku: string, barcode: string, category: string, price: int, stock: int, deal: bool, image: string, description: string}> */
    private function products(): array
    {
        return [
            ['name' => 'Premium Long Grain Rice 5kg', 'sku' => 'RICE-5KG', 'barcode' => '2000000000011', 'category' => 'groceries-pantry', 'price' => 145, 'stock' => 30, 'deal' => true, 'image' => 'images/products/porridge.svg', 'description' => 'Everyday long-grain rice for family meals.'],
            ['name' => 'Gari Ijebu 1kg', 'sku' => 'GARI-1KG', 'barcode' => '2000000000012', 'category' => 'local-traditional-foods', 'price' => 28, 'stock' => 45, 'deal' => false, 'image' => 'images/products/porridge.svg', 'description' => 'Crisp gari, ideal for soaking or eba.'],
            ['name' => 'Tinned Tomato Paste 400g', 'sku' => 'TOMATO-400G', 'barcode' => '2000000000013', 'category' => 'groceries-pantry', 'price' => 16, 'stock' => 60, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Rich tomato paste for stews, jollof and sauces.'],
            ['name' => 'Vegetable Cooking Oil 1L', 'sku' => 'OIL-1L', 'barcode' => '2000000000014', 'category' => 'groceries-pantry', 'price' => 38, 'stock' => 40, 'deal' => true, 'image' => 'storage/products/demo/Gj2VdkKkMdgUagZrkDM8kCKdEMfWmGBWXUrTca0g.jpg', 'description' => 'Versatile vegetable cooking oil for everyday meals.'],
            ['name' => 'Instant Noodles Chicken 70g', 'sku' => 'NOODLES-70G', 'barcode' => '2000000000015', 'category' => 'groceries-pantry', 'price' => 7, 'stock' => 100, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Quick chicken-flavoured instant noodles.'],
            ['name' => 'Malted Cocoa Drink 500g', 'sku' => 'MALT-500G', 'barcode' => '2000000000016', 'category' => 'groceries-pantry', 'price' => 62, 'stock' => 25, 'deal' => false, 'image' => 'images/products/porridge.svg', 'description' => 'Malted cocoa beverage powder for breakfast.'],
            ['name' => 'Sachet Water 500ml (Bag of 30)', 'sku' => 'WATER-30PK', 'barcode' => '2000000000017', 'category' => 'beverages', 'price' => 18, 'stock' => 50, 'deal' => true, 'image' => 'images/products/water.svg', 'description' => 'Chilled drinking water sachets, bag of 30.'],
            ['name' => 'Mineral Water 1.5L', 'sku' => 'WATER-15L', 'barcode' => '2000000000018', 'category' => 'beverages', 'price' => 8, 'stock' => 70, 'deal' => false, 'image' => 'images/products/water.svg', 'description' => 'Refreshing bottled mineral water.'],
            ['name' => 'Malt Drink Can 330ml', 'sku' => 'MALT-330ML', 'barcode' => '2000000000019', 'category' => 'beverages', 'price' => 12, 'stock' => 55, 'deal' => false, 'image' => 'images/products/water.svg', 'description' => 'Non-alcoholic malt beverage, 330ml can.'],
            ['name' => 'Canned Soft Drink 330ml', 'sku' => 'SODA-330ML', 'barcode' => '2000000000020', 'category' => 'beverages', 'price' => 10, 'stock' => 80, 'deal' => true, 'image' => 'images/products/water.svg', 'description' => 'Cold, fizzy soft drink in a 330ml can.'],
            ['name' => 'Plantain (Ripe) 1kg', 'sku' => 'PLANTAIN-1KG', 'barcode' => '2000000000021', 'category' => 'fresh-produce', 'price' => 30, 'stock' => 35, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Fresh ripe plantain, sold by weight.'],
            ['name' => 'Fresh Tomatoes 1kg', 'sku' => 'TOMATO-FRESH-1KG', 'barcode' => '2000000000022', 'category' => 'fresh-produce', 'price' => 35, 'stock' => 30, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Fresh market tomatoes for everyday cooking.'],
            ['name' => 'Red Onions 1kg', 'sku' => 'ONION-1KG', 'barcode' => '2000000000023', 'category' => 'fresh-produce', 'price' => 28, 'stock' => 30, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Fresh red onions, sold by weight.'],
            ['name' => 'Frozen Chicken Drumsticks 1kg', 'sku' => 'CHICKEN-1KG', 'barcode' => '2000000000024', 'category' => 'meat-fish-seafood', 'price' => 72, 'stock' => 20, 'deal' => true, 'image' => 'images/products/catalogue.svg', 'description' => 'Convenient frozen chicken drumsticks, 1kg pack.'],
            ['name' => 'Bathing Soap Bar 175g', 'sku' => 'SOAP-175G', 'barcode' => '2000000000025', 'category' => 'personal-care-beauty', 'price' => 12, 'stock' => 65, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Gentle everyday bathing soap bar.'],
            ['name' => 'Toothpaste Fresh Mint 140g', 'sku' => 'TOOTHPASTE-140G', 'barcode' => '2000000000026', 'category' => 'personal-care-beauty', 'price' => 24, 'stock' => 45, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Fresh mint toothpaste for daily oral care.'],
            ['name' => 'Laundry Detergent Powder 1kg', 'sku' => 'DETERGENT-1KG', 'barcode' => '2000000000027', 'category' => 'household-cleaning', 'price' => 42, 'stock' => 35, 'deal' => true, 'image' => 'images/products/catalogue.svg', 'description' => 'Powerful laundry detergent for bright, clean clothes.'],
            ['name' => 'Dishwashing Liquid 500ml', 'sku' => 'DISHWASH-500ML', 'barcode' => '2000000000028', 'category' => 'household-cleaning', 'price' => 22, 'stock' => 40, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Concentrated liquid for sparkling dishes.'],
            ['name' => 'Tissue Roll Pack of 4', 'sku' => 'TISSUE-4PK', 'barcode' => '2000000000029', 'category' => 'household-cleaning', 'price' => 25, 'stock' => 50, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Soft household tissue, four-roll pack.'],
            ['name' => 'Sanitary Pads Regular 10 Pack', 'sku' => 'PADS-10PK', 'barcode' => '2000000000030', 'category' => 'personal-care-beauty', 'price' => 30, 'stock' => 35, 'deal' => false, 'image' => 'images/products/catalogue.svg', 'description' => 'Comfortable regular sanitary pads, pack of 10.'],
        ];
    }

    private function seedPhotoForCategory(string $category): string
    {
        return match ($category) {
            'fresh-produce' => 'storage/products/demo/seed/produce.jpg',
            'household-cleaning', 'personal-care-beauty' => 'storage/products/demo/seed/cleaning.jpg',
            default => 'storage/products/demo/seed/groceries.jpg',
        };
    }
}
