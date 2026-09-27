<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
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
        $this->actingAs($admin)->get($categoryUrl)->assertStatus(402);
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
            'metadata' => ['features' => ['pos', 'online_store', 'restaurant_foodstore']],
        ]);

        $admin = User::factory()->create([
            'username' => 'ES999-owner',
            'tenant_id' => $this->createdTenant->id,
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect('/sample-foodstore/dashboard');

        $this->actingAs($admin)
            ->get('/sample-foodstore/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('adminDashboard', true)
                ->where('user.role', 'admin')
                ->where('access.onlineStore', true)
                ->where('access.pos', true)
                ->where('access.restaurantFoodStore', true));
    }
}