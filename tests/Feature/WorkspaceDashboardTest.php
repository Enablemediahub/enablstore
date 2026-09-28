<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class WorkspaceDashboardTest extends TestCase
{
    use RefreshDatabase;

    private ?Tenant $createdTenant = null;

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        $this->createdTenant?->delete();

        parent::tearDown();
    }

    public function test_central_dashboard_defaults_to_the_demo_tenant(): void
    {
        DB::table('tenants')->insert([
            'id' => 'the-meat-box',
            'name' => 'The Meat Box',
            'slug' => 'the-meat-box',
            'email' => 'meatbox@example.test',
            'status' => 'active',
            'data' => json_encode(['name' => 'The Meat Box', 'slug' => 'the-meat-box']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('tenants')->insert([
            'id' => 'demo',
            'name' => 'Demo Store',
            'slug' => 'demo',
            'email' => 'demo@example.test',
            'status' => 'active',
            'data' => json_encode(['name' => 'Demo Store', 'slug' => 'demo']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('tenant.id', 'demo')
                ->where('tenant.slug', 'demo'));
    }

    public function test_categories_are_available_with_either_pos_or_foodstore_access(): void
    {
        $this->createdTenant = Tenant::query()->create([
            'id' => 'category-portals',
            'subscriber_code' => 'ES904',
            'name' => 'Category Portals',
            'slug' => 'category-portals',
            'email' => 'categories@example.test',
            'status' => 'active',
            'data' => ['subscriber_code' => 'ES904'],
        ]);
        $plan = Plan::query()->create([
            'name' => 'Category portal plan',
            'slug' => 'category-portal-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => [],
            'is_active' => true,
        ]);
        $subscription = Subscription::query()->create([
            'tenant_id' => $this->createdTenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['pos']],
        ]);
        $admin = User::factory()->create([
            'username' => 'ES904-owner',
            'tenant_id' => $this->createdTenant->id,
            'role' => 'admin',
        ]);

        $categoryUrl = route('tenant.categories.index', ['tenant' => $this->createdTenant->id], false);
        $this->actingAs($admin)->get($categoryUrl)->assertOk();

        $subscription->update(['metadata' => ['features' => ['restaurant_foodstore']]]);
        $this->actingAs($admin)->get($categoryUrl)->assertOk();

        $subscription->update(['metadata' => ['features' => []]]);
        $this->actingAs($admin)
            ->get($categoryUrl)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/FeatureDenied')
                ->where('featureName', 'POS and FoodStore features')
                ->where('dashboardUrl', route('dashboard')));
    }

    public function test_admin_can_delete_a_category_without_deleting_its_products(): void
    {
        $this->createdTenant = Tenant::query()->create([
            'id' => 'category-delete-test',
            'subscriber_code' => 'ES905',
            'name' => 'Category Delete Test',
            'slug' => 'category-delete-test',
            'email' => 'category-delete@example.test',
            'status' => 'active',
            'data' => ['subscriber_code' => 'ES905'],
        ]);
        $plan = Plan::query()->create([
            'name' => 'Category Delete Plan',
            'slug' => 'category-delete-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['pos'],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $this->createdTenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['pos']],
        ]);
        $admin = User::factory()->create([
            'username' => 'ES905-owner',
            'tenant_id' => $this->createdTenant->id,
            'role' => 'admin',
        ]);
        app(Tenancy::class)->initialize($this->createdTenant);
        $category = Category::query()->create(['name' => 'Grill', 'slug' => 'grill']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Meat box',
            'slug' => 'meat-box',
            'sku' => 'MEAT-BOX',
            'price_minor' => 5000,
        ]);

        $this->actingAs($admin)
            ->delete(route('tenant.categories.destroy', ['tenant' => $this->createdTenant->id, 'category' => $category->id]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Category deleted. Linked products are now uncategorized.');

        $this->actingAs($admin)
            ->get(route('tenant.categories.index', ['tenant' => $this->createdTenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Categories/Index')
                ->where('success', 'Category deleted. Linked products are now uncategorized.'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertSame($product->id, $product->fresh()->id);
        $this->assertNull($product->fresh()->category_id);
    }

    public function test_dashboard_uses_the_tenant_selected_in_superadmin_session(): void
    {
        foreach ([['demo', 'Demo Market'], ['royal', 'Royal Supermarket']] as [$id, $name]) {
            DB::table('tenants')->insert([
                'id' => $id,
                'name' => $name,
                'slug' => $id,
                'email' => $id.'@example.test',
                'status' => 'active',
                'data' => json_encode(['name' => $name, 'slug' => $id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $royal = Tenant::query()->findOrFail('royal');
        $plan = Plan::query()->create([
            'name' => 'Royal test plan',
            'slug' => 'royal-test-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => [],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $royal->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['restaurant_foodstore']],
        ]);

        $this->withSession(['workspace_tenant_id' => 'royal'])
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('tenant.id', 'royal')
                ->where('access.restaurantFoodStore', true));
    }

    public function test_authenticated_tenant_admin_can_open_dashboard_with_all_entitlements(): void
    {
        $this->createdTenant = Tenant::query()->create([
            'id' => 'sample-foodstore',
            'subscriber_code' => 'ES903',
            'name' => 'Sample FoodStore',
            'slug' => 'sample-foodstore',
            'email' => 'owner@example.test',
            'status' => 'active',
            'data' => ['name' => 'Sample FoodStore', 'slug' => 'sample-foodstore'],
        ]);

        $plan = Plan::query()->create([
            'name' => 'Test plan',
            'slug' => 'test-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['pos', 'online_store'],
            'is_active' => true,
        ]);

        Subscription::query()->create([
            'tenant_id' => $this->createdTenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['pos', 'online_store', 'restaurant_foodstore', 'foodstore_online']],
        ]);

        $admin = User::factory()->create([
            'username' => 'ES999-owner',
            'tenant_id' => $this->createdTenant->id,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect('/dashboard/sample-foodstore');

        $this->actingAs($admin)
            ->get('/dashboard/sample-foodstore')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('adminDashboard', true)
                ->where('user.role', 'admin')
                ->where('access.onlineStore', true)
                ->where('access.pos', true)
                ->where('access.restaurantFoodStore', true)
                ->where('access.foodstoreOnline', true));

        $this->actingAs($admin)
            ->get('/sample-foodstore/dashboard')
            ->assertOk();
    }

    public function test_authenticated_cashier_dashboard_includes_foodstore_online_entitlement(): void
    {
        $this->createdTenant = Tenant::query()->create([
            'id' => 'cashier-foodstore-online',
            'subscriber_code' => 'ES906',
            'name' => 'Cashier FoodStore',
            'slug' => 'cashier-foodstore-online',
            'email' => 'cashier-foodstore@example.test',
            'status' => 'active',
            'data' => ['name' => 'Cashier FoodStore', 'slug' => 'cashier-foodstore-online'],
        ]);
        $plan = Plan::query()->create([
            'name' => 'Cashier FoodStore plan',
            'slug' => 'cashier-foodstore-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => [],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $this->createdTenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['foodstore_online']],
        ]);
        $cashier = User::factory()->create([
            'username' => 'ES906-cashier',
            'tenant_id' => $this->createdTenant->id,
            'role' => 'cashier',
        ]);

        $this->actingAs($cashier)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('access.foodstoreOnline', true));
    }
}