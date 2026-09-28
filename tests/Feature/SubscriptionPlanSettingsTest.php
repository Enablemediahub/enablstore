<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class SubscriptionPlanSettingsTest extends TestCase
{
    use RefreshDatabase;

    private ?Tenant $whatsappTenant = null;

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        $this->whatsappTenant?->delete();

        parent::tearDown();
    }

    public function test_superadmin_can_create_and_update_month_based_subscription_plans(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.subscriptions.settings'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('SuperAdmin/SubscriptionSettings'));

        $this->actingAs($admin, 'super_admin')->post(route('super-admin.subscriptions.plans.store'), [
            'name' => 'Quarterly Retail',
            'description' => 'Three months of service.',
            'price_ghs' => '180.50',
            'billing_interval_months' => 3,
            'features' => ['pos', 'online_store', 'sales_expenses', 'audit_log', 'whatsapp_orders'],
            'is_active' => true,
        ])->assertRedirect();

        $plan = Plan::query()->where('name', 'Quarterly Retail')->firstOrFail();
        $this->assertSame(18050, $plan->price_minor);
        $this->assertSame(3, $plan->billing_interval_months);
        $this->assertSame('quarterly', $plan->billing_interval);
        $this->assertSame(['pos', 'online_store', 'sales_expenses', 'audit_log', 'whatsapp_orders'], $plan->features);

        $this->actingAs($admin, 'super_admin')->patch(route('super-admin.subscriptions.plans.update', ['plan' => $plan->id]), [
            'name' => 'Half-year Retail',
            'description' => null,
            'price_ghs' => '300',
            'billing_interval_months' => 6,
            'features' => ['restaurant_foodstore', 'sales_expenses', 'audit_log', 'whatsapp_orders'],
            'is_active' => false,
        ])->assertRedirect();

        $plan->refresh();
        $this->assertSame('Half-year Retail', $plan->name);
        $this->assertSame(30000, $plan->price_minor);
        $this->assertSame(6, $plan->billing_interval_months);
        $this->assertSame(['restaurant_foodstore', 'sales_expenses', 'audit_log', 'whatsapp_orders'], $plan->features);
        $this->assertFalse($plan->is_active);
    }

    public function test_registration_uses_the_selected_active_plan_price(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Six Month Store',
            'slug' => 'six-month-store',
            'price_minor' => 24000,
            'currency' => 'GHS',
            'billing_interval' => 'semiannual',
            'billing_interval_months' => 6,
            'features' => ['pos', 'online_store'],
            'is_active' => true,
        ]);

        $this->get(route('tenant.register'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Register')
                ->where('plans.0.id', $plan->id)
                ->where('plans.0.billing_interval_months', 6));

        $this->post(route('tenant.register.store'), [
            'business_name' => 'Trial Store',
            'slug' => 'trial-store-'.Str::lower(Str::random(6)),
            'email' => 'owner@example.test',
            'phone' => '+233201234567',
            'owner_name' => 'Store Owner',
            'plan_id' => $plan->id,
        ])->assertRedirect();

        $subscription = Subscription::query()->where('plan_id', $plan->id)->latest()->firstOrFail();
        $this->assertSame(24000, $subscription->amount_minor);
        $this->assertSame(6, $subscription->metadata['trial_billing_interval_months']);
    }

    public function test_superadmin_enrollment_uses_the_selected_plan_amount_and_duration(): void
    {
        $admin = $this->createSuperAdmin();
        $businessName = 'Annual Workspace '.Str::lower(Str::random(8));
        $plan = Plan::query()->create([
            'name' => 'Annual Store',
            'slug' => 'annual-store',
            'price_minor' => 120000,
            'currency' => 'GHS',
            'billing_interval' => 'yearly',
            'billing_interval_months' => 12,
            'features' => ['pos'],
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'super_admin')
            ->post(route('super-admin.users.store'), [
                'name' => 'Workspace Admin',
                'username' => 'workspace-admin',
                'email' => 'workspace-admin@example.test',
                'password' => 'correct-password',
                'business_name' => $businessName,
                'plan_id' => $plan->id,
                'features' => ['sales_expenses', 'audit_log', 'whatsapp_orders'],
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Workspace user created.');

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.tenants.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SuperAdmin/Tenants')
                ->where('status', 'Workspace user created.'));

        $tenant = Tenant::query()->findOrFail(Str::slug($businessName));
        $subscription = Subscription::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame(120000, $subscription->amount_minor);
        $this->assertSame('active', $subscription->status);
        $this->assertSame(['sales_expenses', 'audit_log', 'whatsapp_orders'], $subscription->metadata['features']);
        $this->assertTrue($subscription->renews_at->greaterThan(now()->addMonthsNoOverflow(11)));
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'provider' => 'manual',
            'amount_minor' => 120000,
            'status' => 'paid',
        ]);
        $tenant->delete();
    }

    public function test_superadmin_can_register_a_tenant_whatsapp_number(): void
    {
        $admin = $this->createSuperAdmin();
        $tenantId = 'whatsapp-'.Str::lower(Str::random(8));
        $this->whatsappTenant = Tenant::query()->create([
            'id' => $tenantId,
            'subscriber_code' => 'ES907',
            'name' => 'WhatsApp Store',
            'slug' => $tenantId,
            'email' => 'whatsapp-store@example.test',
            'status' => 'active',
            'data' => ['name' => 'WhatsApp Store', 'slug' => $tenantId],
        ]);
        $plan = Plan::query()->create([
            'name' => 'WhatsApp Store plan',
            'slug' => 'whatsapp-store-plan',
            'price_minor' => 10000,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => [],
            'is_active' => true,
        ]);
        $subscription = Subscription::query()->create([
            'tenant_id' => $this->whatsappTenant->id,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => ['online_store']],
        ]);

        $this->actingAs($admin, 'super_admin')
            ->patch(route('super-admin.tenants.update', ['tenant' => $this->whatsappTenant->id]), [
                'name' => 'WhatsApp Store',
                'email' => 'whatsapp-store@example.test',
                'phone' => '+233201111111',
                'whatsapp_phone' => '+233 20 222 3333',
                'status' => 'active',
                'team_management_enabled' => false,
                'catalogue_mode' => 'shared',
                'features' => ['online_store', 'sales_expenses', 'audit_log', 'whatsapp_orders'],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('+233 20 222 3333', $this->whatsappTenant->fresh()->whatsapp_phone);
        $this->assertFalse($this->whatsappTenant->fresh()->teamManagementEnabled());
        $this->assertSame(['online_store', 'sales_expenses', 'audit_log', 'whatsapp_orders'], $subscription->fresh()->metadata['features']);
        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.tenants.show', ['tenant' => $this->whatsappTenant->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SuperAdmin/Tenant')
                ->where('tenant.whatsapp_phone', '+233 20 222 3333')
                ->where('tenant.team_management_enabled', false)
                ->where('plans.0.price_minor', 10000)
                ->where('plans.0.currency', 'GHS'));

    }

    private function createSuperAdmin(): SuperAdmin
    {
        return SuperAdmin::query()->create([
            'name' => 'Subscription Admin',
            'email' => 'plans-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);
    }
}