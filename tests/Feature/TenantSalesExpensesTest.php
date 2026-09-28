<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class TenantSalesExpensesTest extends TestCase
{
    use RefreshDatabase;

    private ?Tenant $createdTenant = null;

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        $this->createdTenant?->delete();

        parent::tearDown();
    }

    public function test_sales_expenses_page_requires_access_and_tracks_online_pos_and_expenses(): void
    {
        $this->withoutVite();
        $tenantId = 'sales-expenses-'.Str::lower(Str::random(8));
        $this->createdTenant = Tenant::query()->create([
            'id' => $tenantId,
            'subscriber_code' => 'ES915',
            'name' => 'Sales Expenses Store',
            'slug' => $tenantId,
            'email' => 'sales-expenses@example.test',
            'status' => 'active',
            'data' => ['subscriber_code' => 'ES915'],
        ]);
        $plan = Plan::query()->create([
            'name' => 'Sales expenses plan',
            'slug' => 'sales-expenses-plan-'.Str::lower(Str::random(5)),
            'price_minor' => 10000,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['sales_expenses'],
            'is_active' => true,
        ]);
        $subscription = Subscription::query()->create([
            'tenant_id' => $tenantId,
            'plan_id' => $plan->id,
            'provider' => 'internal',
            'status' => 'active',
            'starts_at' => now(),
            'metadata' => ['features' => []],
        ]);
        $admin = User::factory()->create([
            'username' => 'ES915-owner',
            'tenant_id' => $tenantId,
            'role' => 'admin',
        ]);
        $url = route('tenant.sales-expenses.index', ['tenant' => $tenantId]);

        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/FeatureDenied')
                ->where('featureName', 'Sales and expenses'));
        $this->actingAs($admin)->get(route('tenant.audit', ['tenant' => $tenantId]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/FeatureDenied')
                ->where('featureName', 'Audit Log'));

        $subscription->update(['metadata' => ['features' => ['sales_expenses']]]);
        $this->createdTenant->run(function (): void {
            Sale::query()->create([
                'transaction_uuid' => (string) Str::uuid(),
                'subtotal_minor' => 10000,
                'discount_minor' => 0,
                'total_minor' => 10000,
                'payment_method' => 'mobile_money',
                'source' => 'storefront',
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            Sale::query()->create([
                'transaction_uuid' => (string) Str::uuid(),
                'subtotal_minor' => 20000,
                'discount_minor' => 0,
                'total_minor' => 20000,
                'payment_method' => 'cash',
                'source' => 'pos',
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/SalesExpenses/Index')
                ->where('metrics.sales_count', 2)
                ->where('metrics.online_revenue_minor', 10000)
                ->where('metrics.pos_revenue_minor', 20000)
                ->where('metrics.revenue_minor', 30000)
                ->where('metrics.expenses_minor', 0));

        $this->actingAs($admin)->post(route('tenant.sales-expenses.store', ['tenant' => $tenantId]), [
            'category' => 'Transport',
            'description' => 'Supplier delivery',
            'amount_ghs' => '12.50',
            'payment_method' => 'cash',
            'spent_at' => now()->toDateString(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $expense = $this->createdTenant->run(fn (): Expense => Expense::query()->firstOrFail());
        $this->assertSame('Transport', $expense->category);
        $this->assertSame(1250, $expense->amount_minor);

        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('metrics.expenses_minor', 1250)
                ->where('metrics.net_minor', 28750));

        $subscription->update(['metadata' => ['features' => ['sales_expenses', 'audit_log']]]);
        $this->actingAs($admin)->get(route('tenant.audit', ['tenant' => $tenantId]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Tenant/Audit/Index'));
    }
}