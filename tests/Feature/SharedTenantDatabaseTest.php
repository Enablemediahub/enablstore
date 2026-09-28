<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class SharedTenantDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<Tenant> */
    private array $tenants = [];

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        foreach ($this->tenants as $tenant) {
            $tenant->delete();
        }

        parent::tearDown();
    }

    public function test_tenant_owned_products_are_scoped_and_allow_tenant_local_skus(): void
    {
        $firstTenant = $this->createTenant('shared-first', 'ES930');
        $secondTenant = $this->createTenant('shared-second', 'ES931');
        $this->assertSame('ES930', DB::table('tenants')->where('id', 'shared-first')->value('subscriber_code'));
        $this->assertSame('ES931', DB::table('tenants')->where('id', 'shared-second')->value('subscriber_code'));

        app(Tenancy::class)->initialize($firstTenant);
        $firstCategory = Category::query()->create(['name' => 'Snacks', 'slug' => 'snacks']);
        $firstProduct = Product::query()->create([
            'category_id' => $firstCategory->id,
            'name' => 'Sample item',
            'slug' => 'sample-item',
            'sku' => 'SAMPLE-001',
            'price_minor' => 2500,
        ]);
        $this->assertSame('shared-first', $firstProduct->tenant_id);

        app(Tenancy::class)->end();
        app(Tenancy::class)->initialize($secondTenant);
        $secondCategory = Category::query()->create(['name' => 'Snacks', 'slug' => 'snacks']);
        $secondProduct = Product::query()->create([
            'category_id' => $secondCategory->id,
            'name' => 'Sample item',
            'slug' => 'sample-item',
            'sku' => 'SAMPLE-001',
            'price_minor' => 2500,
        ]);

        $this->assertNotSame($firstProduct->id, $secondProduct->id);
        $this->assertSame('shared-second', $secondProduct->tenant_id);
        $this->assertSame([$secondProduct->id], Product::query()->pluck('id')->all());
        $this->assertDatabaseHas('products', ['id' => $firstProduct->id, 'tenant_id' => 'shared-first']);
        $this->assertDatabaseHas('products', ['id' => $secondProduct->id, 'tenant_id' => 'shared-second']);
        $this->assertSame(14, DB::table('categories')->where('tenant_id', 'shared-first')->count() - 1);
        $this->assertSame(14, DB::table('categories')->where('tenant_id', 'shared-second')->count() - 1);

        app(Tenancy::class)->end();
        $this->assertSame(0, Product::query()->count());
    }

    private function createTenant(string $id, string $subscriberCode): Tenant
    {
        $tenant = Tenant::query()->create([
            'id' => $id,
            'subscriber_code' => $subscriberCode,
            'name' => $id,
            'slug' => $id,
            'email' => $id.'@example.test',
            'status' => 'active',
            'data' => [],
        ]);
        $this->tenants[] = $tenant;

        return $tenant;
    }
}