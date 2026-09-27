<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
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

class SuperAdminFinancialTest extends TestCase
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

    public function test_financial_page_reports_subscription_sales_and_filters_results(): void
    {
        $admin = SuperAdmin::query()->create([
            'name' => 'Financial Admin',
            'email' => 'financial-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);
        $plan = Plan::query()->create([
            'name' => 'Financial Monthly',
            'slug' => 'financial-monthly',
            'price_minor' => 10000,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'billing_interval_months' => 1,
            'features' => ['pos'],
            'is_active' => true,
        ]);
        $suffix = Str::lower(Str::random(8));
        $alpha = Tenant::query()->create([
            'id' => 'finance-alpha-'.$suffix,
            'subscriber_code' => 'ES901',
            'name' => 'Alpha Subscriber',
            'slug' => 'alpha-subscriber-'.$suffix,
            'email' => 'alpha@example.test',
            'phone' => '+233201234567',
            'status' => 'active',
        ]);
        $beta = Tenant::query()->create([
            'id' => 'finance-beta-'.$suffix,
            'subscriber_code' => 'ES902',
            'name' => 'Beta Subscriber',
            'slug' => 'beta-subscriber-'.$suffix,
            'email' => 'beta@example.test',
            'status' => 'active',
        ]);
        $this->tenants = [$alpha, $beta];
        $alphaSubscription = Subscription::query()->create([
            'tenant_id' => $alpha->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'provider' => 'internal',
            'starts_at' => now()->subMonth(),
            'renews_at' => now()->addDays(3),
        ]);
        $betaSubscription = Subscription::query()->create([
            'tenant_id' => $beta->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'provider' => 'internal',
            'starts_at' => now()->subMonth(),
            'renews_at' => now()->addMonth(),
        ]);

        Payment::query()->create([
            'tenant_id' => $alpha->id,
            'subscription_id' => $alphaSubscription->id,
            'provider' => 'paystack',
            'provider_reference' => 'finance-alpha-paid',
            'amount_minor' => 10000,
            'currency' => 'GHS',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        Payment::query()->create([
            'tenant_id' => $beta->id,
            'subscription_id' => $betaSubscription->id,
            'provider' => 'manual',
            'provider_reference' => 'finance-beta-paid',
            'amount_minor' => 20000,
            'currency' => 'GHS',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        Payment::query()->create([
            'tenant_id' => $alpha->id,
            'subscription_id' => $alphaSubscription->id,
            'provider' => 'paystack',
            'provider_reference' => 'finance-alpha-pending',
            'amount_minor' => 5000,
            'currency' => 'GHS',
            'status' => 'pending',
        ]);
        Payment::query()->create([
            'tenant_id' => $alpha->id,
            'subscription_id' => null,
            'provider' => 'paystack',
            'provider_reference' => 'finance-pos-sale',
            'amount_minor' => 90000,
            'currency' => 'GHS',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.financial.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SuperAdmin/Financial')
                ->where('metrics.revenue_minor', 30000)
                ->where('metrics.paid_count', 2)
                ->where('metrics.paystack_minor', 10000)
                ->where('metrics.manual_minor', 20000)
                ->where('metrics.pending_count', 1)
                ->where('metrics.upcoming_renewals_count', 1)
                ->where('payments.data.0.expires_at', $alphaSubscription->renews_at->toIso8601String())
                ->has('payments.data', 3));

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.financial.index', [
                'search' => 'Alpha',
                'provider' => 'paystack',
                'status' => 'paid',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'Alpha')
                ->where('metrics.revenue_minor', 10000)
                ->where('metrics.paid_count', 1)
                ->has('payments.data', 1)
                ->where('payments.data.0.reference', 'finance-alpha-paid'));

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.financial.index', ['renewal_window' => '7_days']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.renewal_window', '7_days')
                ->where('metrics.upcoming_renewals_count', 1)
                ->has('payments.data', 2)
                ->where('payments.data.0.phone', '+233201234567')
                ->where('payments.data.0.expires_at', $alphaSubscription->renews_at->toIso8601String()));
    }
}