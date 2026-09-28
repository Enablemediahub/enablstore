<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\RestaurantMenuItem;
use App\Models\Category;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\RestaurantOrder;
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

    public function test_foodstore_checkout_calculates_and_snapshots_selected_options(): void
    {
        $this->startTenant();
        $menuItem = RestaurantMenuItem::query()->create([
            'name' => 'Peanut soup bowl',
            'category' => 'Soup',
            'price_minor' => 4000,
            'is_available' => true,
            'option_groups' => [[
                'id' => 'protein',
                'name' => 'Protein',
                'required' => true,
                'multiple' => false,
                'options' => [
                    ['id' => 'goat', 'name' => 'Goat meat', 'price_minor' => 2500],
                    ['id' => 'chicken', 'name' => 'Chicken', 'price_minor' => 1500],
                ],
            ], [
                'id' => 'extras',
                'name' => 'Extras',
                'required' => false,
                'multiple' => true,
                'options' => [['id' => 'egg', 'name' => 'Boiled egg', 'price_minor' => 700]],
            ]],
        ]);

        $sale = app(CheckoutService::class)->checkout([
            'transaction_uuid' => (string) Str::uuid(),
            'payment_method' => 'cash',
            'source' => 'foodstore',
            'items' => [[
                'menu_item_id' => $menuItem->id,
                'quantity' => 2,
                'selected_options' => [
                    ['id' => 'goat', 'quantity' => 2],
                    ['id' => 'egg', 'quantity' => 1],
                ],
            ]],
            'tenders' => [[
                'method' => 'cash',
                'amount_minor' => 19400,
                'cash_received_minor' => 19400,
            ]],
        ]);

        $saleItem = $sale->items()->firstOrFail();
        $this->assertSame(19400, $sale->total_minor);
        $this->assertSame(9700, $saleItem->unit_price_minor);
        $this->assertSame([
            ['group' => 'Protein', 'name' => 'Goat meat', 'price_minor' => 2500, 'quantity' => 2],
            ['group' => 'Extras', 'name' => 'Boiled egg', 'price_minor' => 700, 'quantity' => 1],
        ], $saleItem->selected_options);
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

    public function test_foodstore_checkout_records_discount_and_split_tenders(): void
    {
        $this->startTenant();
        $menuItem = RestaurantMenuItem::query()->create([
            'name' => 'Grilled meat box',
            'category' => 'Grill',
            'price_minor' => 5000,
            'is_available' => true,
        ]);

        $sale = app(CheckoutService::class)->checkout([
            'transaction_uuid' => (string) Str::uuid(),
            'payment_method' => 'split',
            'source' => 'foodstore',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'discount_reason' => 'Loyalty discount',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2]],
            'tenders' => [
                ['method' => 'cash', 'amount_minor' => 5000, 'cash_received_minor' => 6000],
                ['method' => 'mobile_money', 'amount_minor' => 4000, 'cash_received_minor' => null, 'externally_confirmed' => true],
            ],
        ]);

        $this->assertSame(10000, $sale->subtotal_minor);
        $this->assertSame(1000, $sale->discount_minor);
        $this->assertSame('Loyalty discount', $sale->discount_reason);
        $this->assertSame(9000, $sale->total_minor);
        $this->assertSame('split', $sale->payment_method);
        $this->assertDatabaseCount('sale_payments', 2);
        $this->assertDatabaseHas('sale_payments', [
            'sale_id' => $sale->id,
            'method' => 'cash',
            'amount_minor' => 5000,
            'cash_received_minor' => 6000,
        ]);
        $this->assertDatabaseHas('sale_payments', [
            'sale_id' => $sale->id,
            'method' => 'mobile_money',
            'amount_minor' => 4000,
            'externally_confirmed' => true,
        ]);
    }

    public function test_customer_can_place_a_foodstore_online_order(): void
    {
        $this->startTenant();
        app(Tenancy::class)->end();
        $plan = Plan::query()->create([
            'name' => 'FoodStore online test plan',
            'slug' => 'foodstore-online-test-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['restaurant_foodstore', 'foodstore_online'],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['restaurant_foodstore', 'foodstore_online']],
        ]);
        $this->tenant->update(['whatsapp_phone' => '+233 20 123 4567']);
        app(Tenancy::class)->initialize($this->tenant);
        $menuItem = RestaurantMenuItem::query()->create([
            'name' => 'Family meat box',
            'category' => 'Grill',
            'price_minor' => 12500,
            'unit_label' => 'pack',
            'is_available' => true,
        ]);

        $this->get(route('tenant.foodstore.online', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Restaurant/Online')
                ->where('whatsappPhone', '+233 20 123 4567')
                ->where('menuItems.0.name', 'Family meat box'));

        $this->post(route('tenant.foodstore.online.orders.store', ['tenant' => $this->tenant->id]), [
            'customer_name' => 'Online Customer',
            'customer_phone' => '+233201234567',
            'notes' => 'Please call on arrival.',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2]],
        ])->assertRedirect()->assertSessionHas('status', 'Your order has been sent to the restaurant.');

        $order = RestaurantOrder::query()->with('items')->firstOrFail();
        $this->assertSame('Online Customer', $order->customer_name);
        $this->assertSame('+233201234567', $order->customer_phone);
        $this->assertSame('Please call on arrival.', $order->notes);
        $this->assertSame('Online customer', $order->created_by_name);
        $this->assertSame(25000, $order->total_minor);
        $this->assertSame('Family meat box', $order->items->first()->item_name);
    }

    public function test_online_order_requires_configured_choices_and_prices_them_on_the_server(): void
    {
        $this->startTenant();
        app(Tenancy::class)->end();
        $plan = Plan::query()->create([
            'name' => 'FoodStore options test plan',
            'slug' => 'foodstore-options-test-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['restaurant_foodstore', 'foodstore_online'],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['restaurant_foodstore', 'foodstore_online']],
        ]);
        app(Tenancy::class)->initialize($this->tenant);
        $menuItem = RestaurantMenuItem::query()->create([
            'name' => 'Rice plate',
            'category' => 'Meals',
            'price_minor' => 3000,
            'is_available' => true,
            'option_groups' => [[
                'id' => 'protein',
                'name' => 'Protein',
                'required' => true,
                'multiple' => false,
                'options' => [
                    ['id' => 'chicken', 'name' => 'Chicken', 'price_minor' => 2000],
                    ['id' => 'goat', 'name' => 'Goat meat', 'price_minor' => 3500],
                ],
            ], [
                'id' => 'extras',
                'name' => 'Extras',
                'required' => false,
                'multiple' => true,
                'options' => [['id' => 'salad', 'name' => 'Salad', 'price_minor' => 500]],
            ]],
        ]);
        $url = route('tenant.foodstore.online.orders.store', ['tenant' => $this->tenant->id]);
        $payload = [
            'customer_name' => 'Online Customer',
            'customer_phone' => '+233201234567',
            'notes' => 'ALLERGY ALERT: Peanuts',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1]],
        ];

        $this->post($url, $payload)->assertSessionHasErrors('items');
        $this->assertDatabaseCount('restaurant_orders', 0);

        $payload['items'][0]['selected_options'] = [
            ['id' => 'chicken', 'quantity' => 2],
            ['id' => 'salad', 'quantity' => 1],
        ];
        $this->post($url, $payload)->assertRedirect();

        $order = RestaurantOrder::query()->with('items')->firstOrFail();
        $orderItem = $order->items->firstOrFail();
        $this->assertSame('ALLERGY ALERT: Peanuts', $order->notes);
        $this->assertSame(7500, $order->total_minor);
        $this->assertSame(7500, $orderItem->line_total_minor);
        $this->assertSame([
            ['group' => 'Protein', 'name' => 'Chicken', 'price_minor' => 2000, 'quantity' => 2],
            ['group' => 'Extras', 'name' => 'Salad', 'price_minor' => 500, 'quantity' => 1],
        ], $orderItem->selected_options);
    }

    public function test_whatsapp_order_is_saved_to_the_kitchen_queue_and_can_be_prepared(): void
    {
        $this->startTenant();
        app(Tenancy::class)->end();
        $plan = Plan::query()->create([
            'name' => 'Kitchen queue test plan',
            'slug' => 'kitchen-queue-test-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['restaurant_foodstore', 'foodstore_online'],
            'is_active' => true,
        ]);
        $subscription = Subscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['restaurant_foodstore', 'foodstore_online']],
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);
        app(Tenancy::class)->initialize($this->tenant);
        $menuItem = RestaurantMenuItem::query()->create([
            'name' => 'Chicken rice plate',
            'category' => 'Meals',
            'price_minor' => 4500,
            'is_available' => true,
        ]);

        $orderUrl = route('tenant.foodstore.online.orders.store', ['tenant' => $this->tenant->id]);
        $orderData = [
            'customer_name' => 'WhatsApp Customer',
            'customer_phone' => '+233201234567',
            'channel' => 'whatsapp',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2]],
        ];
        $this->post($orderUrl, $orderData)->assertStatus(402);
        $subscription->update(['metadata' => ['features' => ['restaurant_foodstore', 'foodstore_online', 'whatsapp_orders']]]);
        $this->post($orderUrl, $orderData)->assertRedirect();

        $order = RestaurantOrder::query()->firstOrFail();
        $this->assertSame('Online customer via WhatsApp', $order->created_by_name);
        $this->actingAs($admin)
            ->get(route('tenant.foodstore.orders.index', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Restaurant/Orders')
                ->where('orders.0.customerName', 'WhatsApp Customer')
                ->where('orders.0.createdByName', 'Online customer via WhatsApp')
                ->where('orders.0.items.0.name', 'Chicken rice plate'));

        $this->actingAs($admin)
            ->patch(route('tenant.foodstore.orders.status', ['tenant' => $this->tenant->id, 'order' => $order->id]), [
                'status' => 'preparing',
            ])
            ->assertRedirect();
        $this->assertSame('preparing', $order->fresh()->status);
    }

    public function test_pos_and_foodstore_heroes_show_separate_completed_sales_for_today(): void
    {
        $this->startTenant();
        app(Tenancy::class)->end();
        $plan = Plan::query()->create([
            'name' => 'Daily totals plan',
            'slug' => 'daily-totals-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['pos', 'restaurant_foodstore'],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['pos', 'restaurant_foodstore']],
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);
        app(Tenancy::class)->initialize($this->tenant);

        foreach ([
            ['source' => 'pos', 'amount' => 12500, 'date' => now()],
            ['source' => 'foodstore', 'amount' => 4600, 'date' => now()],
            ['source' => 'pos', 'amount' => 9900, 'date' => now()->subDay()],
        ] as $index => $entry) {
            Sale::query()->create([
                'transaction_uuid' => (string) Str::uuid(),
                'cashier_name' => 'Test Admin',
                'subtotal_minor' => $entry['amount'],
                'total_minor' => $entry['amount'],
                'currency' => 'GHS',
                'payment_method' => 'cash',
                'source' => $entry['source'],
                'status' => 'completed',
                'completed_at' => $entry['date'],
            ]);
        }

        $this->actingAs($admin)
            ->get(route('tenant.pos', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Pos/Index')
                ->where('todaySalesMinor', 12500)
                ->where('todaySalesCount', 1));

        $this->actingAs($admin)
            ->get(route('tenant.foodstore.index', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Restaurant/Index')
                    ->where('operatorName', $admin->name)
                ->where('todaySalesMinor', 4600)
                ->where('todaySalesCount', 1));
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

    public function test_foodstore_access_denial_renders_the_branded_access_dialog(): void
    {
        $this->startTenant();
        app(Tenancy::class)->end();
        $plan = Plan::query()->create([
            'name' => 'POS-only test plan',
            'slug' => 'pos-only-test-plan',
            'price_minor' => 0,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['pos'],
            'is_active' => true,
        ]);
        Subscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['pos']],
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);
        app(Tenancy::class)->initialize($this->tenant);

        $this->actingAs($admin)
            ->get(route('tenant.foodstore.index', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/FeatureDenied')
                ->where('featureName', 'FoodStore')
                ->where('tenantName', $this->tenant->name)
                ->where('dashboardUrl', route('dashboard')));
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
                'option_groups' => [[
                    'id' => 'protein',
                    'name' => 'Protein',
                    'required' => true,
                    'multiple' => false,
                    'options' => [['id' => 'chicken', 'name' => 'Chicken', 'price_ghs' => '12.00']],
                ]],
                'image' => UploadedFile::fake()->image('jollof.jpg'),
            ])
            ->assertRedirect();

        $menuItem = RestaurantMenuItem::query()->where('name', 'Family jollof pack')->firstOrFail();
        $this->assertSame(9550, $menuItem->price_minor);
        $this->assertSame('pack', $menuItem->unit_label);
        $this->assertSame(1200, $menuItem->option_groups[0]['options'][0]['price_minor']);
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
                ->where('menuItems.0.option_groups.0.name', 'Protein')
                ->where('menuItems.0.image_url', route('tenant.media', [
                    'tenant' => $this->tenant->id,
                    'path' => $menuItem->image_path,
                ])));

        $this->get(route('tenant.media', [
            'tenant' => $this->tenant->id,
            'path' => $menuItem->image_path,
        ]))->assertOk();

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'foodstore_hero_image'],
            ['value' => 'platform/foodstore-hero.jpg'],
        );

        $this->actingAs($admin)
            ->get(route('tenant.foodstore.index', ['tenant' => $this->tenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Restaurant/Index')
                ->where('heroImageUrl', 'http://localhost/storage/platform/foodstore-hero.jpg'));
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
