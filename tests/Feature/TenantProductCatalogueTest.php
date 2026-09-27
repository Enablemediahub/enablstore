<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryStock;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class TenantProductCatalogueTest extends TestCase
{
    use RefreshDatabase;

    private ?Tenant $tenant = null;

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        $this->tenant?->delete();

        parent::tearDown();
    }

    public function test_product_catalogue_summaries_and_filters_cover_all_matching_stock(): void
    {
        $this->tenant = Tenant::query()->create([
            'id' => 'product-catalogue-'.Str::lower(Str::random(6)),
            'subscriber_code' => 'ES905',
            'name' => 'Product Catalogue Test',
            'slug' => 'product-catalogue-test',
            'email' => 'products@example.test',
            'status' => 'active',
            'data' => ['subscriber_code' => 'ES905'],
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);
        app(Tenancy::class)->initialize($this->tenant);

        $food = Category::query()->create(['name' => 'Food', 'slug' => 'food']);
        $pantry = Category::query()->create(['name' => 'Pantry', 'slug' => 'pantry']);
        $this->createProduct('Rice bag', 'RICE-1', $food->id, 1000, 600, 4, 2);
        $this->createProduct('Oil bottle', 'OIL-1', $pantry->id, 2000, 1000, 1, 2);
        $this->createProduct('Water case', 'WATER-1', $pantry->id, 500, 200, 0, 3);

        $url = route('tenant.products.index', ['tenant' => $this->tenant->id], false);
        $this->actingAs($admin)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Products/Index')
                ->where('summary.product_count', 3)
                ->where('summary.units_in_stock', 5)
                ->where('summary.cost_value_minor', 3400)
                ->where('summary.retail_value_minor', 6000)
                ->where('summary.profit_value_minor', 2600)
                ->where('summary.low_stock_count', 1)
                ->where('summary.out_of_stock_count', 1));

        $this->actingAs($admin)
            ->get($url.'?category_id='.$pantry->id.'&stock_status=low_stock')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.product_count', 1)
                ->where('products.data.0.name', 'Oil bottle')
                ->where('filters.category_id', $pantry->id)
                ->where('filters.stock_status', 'low_stock'));
    }

    private function createProduct(string $name, string $sku, int $categoryId, int $priceMinor, int $costMinor, int $quantity, int $threshold): void
    {
        $product = Product::query()->create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => $sku,
            'price_minor' => $priceMinor,
            'cost_minor' => $costMinor,
            'purchase_unit' => 'unit',
            'units_per_purchase' => 1,
            'currency' => 'GHS',
            'is_active' => true,
            'available_in_pos' => true,
            'available_online' => true,
        ]);
        InventoryStock::query()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'low_stock_threshold' => $threshold,
        ]);
    }
}
