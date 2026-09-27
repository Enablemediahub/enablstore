<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\RestaurantMenuItem;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Str;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class FoodStoreSaleCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private ?Tenant $tenant = null;

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        $this->tenant?->delete();

        parent::tearDown();
    }

    public function test_foodstore_checkout_records_a_sale_without_inventory_movements_or_kitchen_orders(): void
    {
        $this->startTenant();
        $menuItem = RestaurantMenuItem::query()->create([
            'name' => 'Jollof rice plate',
            'category' => 'Rice dishes',
            'price_minor' => 3200,
            'is_available' => true,
        ]);

        $sale = app(CheckoutService::class)->checkout([
            'transaction_uuid' => (string) Str::uuid(),
            'payment_method' => 'cash',
            'source' => 'foodstore',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2]],
            'tenders' => [[
                'method' => 'cash',
                'amount_minor' => 6400,
                'cash_received_minor' => 7000,
            ]],
        ]);

        $this->assertSame(6400, $sale->total_minor);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => null,
            'restaurant_menu_item_id' => $menuItem->id,
            'item_name' => 'Jollof rice plate',
            'quantity' => 2,
            'line_total_minor' => 6400,
        ]);
        $this->assertDatabaseHas('sale_payments', [
            'sale_id' => $sale->id,
            'method' => 'cash',
            'amount_minor' => 6400,
            'cash_received_minor' => 7000,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'sale.completed',
            'auditable_id' => (string) $sale->id,
        ]);
        $this->assertSame(0, StockMovement::query()->count());
        $this->assertDatabaseCount('restaurant_orders', 0);
    }

    public function test_foodstore_checkout_rejects_a_paused_menu_item(): void
    {
        $this->startTenant();
        $menuItem = RestaurantMenuItem::query()->create([
            'name' => 'Grilled meat pack',
            'category' => 'Grill',
            'price_minor' => 2500,
            'is_available' => false,
        ]);

        $this->expectException(\DomainException::class);

        app(CheckoutService::class)->checkout([
            'transaction_uuid' => (string) Str::uuid(),
            'payment_method' => 'cash',
            'source' => 'foodstore',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1]],
            'tenders' => [[
                'method' => 'cash',
                'amount_minor' => 2500,
                'cash_received_minor' => 2500,
            ]],
        ]);
    }

    public function test_guest_foodstore_access_opens_the_staff_pin_screen_before_entitlement_check(): void
    {
        $this->startTenant();

        $this->get(route('tenant.foodstore.index', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Pos/Unlock')
                ->where('workspace', 'foodstore'));
    }

    public function test_admin_can_add_a_food_item_with_a_photo_and_package_price(): void
    {
        $this->startTenant();
        app(Tenancy::class)->end();
        $plan = Plan::query()->create([
            'name' => 'FoodStore test plan',
            'slug' => 'foodstore-test-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['restaurant_foodstore'],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['restaurant_foodstore']],
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);
        app(Tenancy::class)->initialize($this->tenant);
        Category::query()->create(['name' => 'Rice dishes', 'slug' => 'rice-dishes']);
        Storage::fake('public');

        $this->actingAs($admin)
            ->post(route('tenant.foodstore.menu.store', ['tenant' => $this->tenant->id]), [
                'name' => 'Family jollof pack',
                'category' => 'Rice dishes',
                'description' => 'Family size pack',
                'price_ghs' => '95.50',
                'unit_label' => 'pack',
                'image' => UploadedFile::fake()->image('jollof.jpg'),
            ])
            ->assertRedirect();

        $menuItem = RestaurantMenuItem::query()->where('name', 'Family jollof pack')->firstOrFail();
        $this->assertSame(9550, $menuItem->price_minor);
        $this->assertSame('pack', $menuItem->unit_label);
        $this->assertNotEmpty($menuItem->image_path);
        Storage::disk('public')->assertExists($menuItem->image_path);
        $categoryNames = Category::query()->orderBy('name')->pluck('name')->values()->all();
        $this->assertContains('Rice dishes', $categoryNames);

        $this->actingAs($admin)
            ->get(route('tenant.foodstore.menu.index', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Restaurant/Menu')
                ->where('categories', $categoryNames)
                ->where('menuItems.0.unit_label', 'pack')
                ->where('menuItems.0.image_url', route('tenant.media', [
                    'tenant' => $this->tenant->id,
                    'path' => $menuItem->image_path,
                ])));

        $this->get(route('tenant.media', [
            'tenant' => $this->tenant->id,
            'path' => $menuItem->image_path,
        ]))->assertOk();
    }

    private function startTenant(): void
    {
        $request = Request::create('/');
        $request->setLaravelSession(app('session')->driver());
        app()->instance('request', $request);

        $id = 'foodstore-sale-'.Str::lower(Str::random(8));
        $this->tenant = Tenant::query()->create([
            'id' => $id,
            'subscriber_code' => 'ES902',
            'name' => 'FoodStore Sales Test',
            'slug' => $id,
            'email' => $id.'@example.test',
            'status' => 'active',
            'data' => ['subscriber_code' => 'ES902'],
        ]);

        app(Tenancy::class)->initialize($this->tenant);
    }
}
