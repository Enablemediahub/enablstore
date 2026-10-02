<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\SuperAdmin\Pages\FinancialOverview;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
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
            ->get('/super-admin/financial-overview')
            ->assertOk()
            ->assertSee('Subscription revenue')
            ->assertSee('GHS 300.00')
            ->assertSee('finance-alpha-paid')
            ->assertDontSee('finance-pos-sale');
        Filament::setCurrentPanel(Filament::getPanel('super-admin'));

        Livewire::test(FinancialOverview::class)
            ->filterTable('provider', 'paystack')
            ->filterTable('status', 'pending')
            ->assertSee('finance-alpha-pending')
            ->assertDontSee('finance-beta-paid');

        Livewire::test(FinancialOverview::class)
            ->searchTable('Alpha')
            ->filterTable('provider', 'paystack')
            ->filterTable('status', 'paid')
            ->assertSee('finance-alpha-paid')
            ->assertDontSee('finance-beta-paid')
            ->assertDontSee('finance-alpha-pending');

        Livewire::test(FinancialOverview::class)
            ->filterTable('upcoming_renewals', true)
            ->assertSee('finance-alpha-paid')
            ->assertSee('finance-alpha-pending')
            ->assertDontSee('finance-beta-paid');

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.financial.index'))
            ->assertRedirect(route('filament.super-admin.pages.financial-overview'));

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.financial.index', [
                'search' => 'Alpha',
                'provider' => 'paystack',
                'status' => 'paid',
            ]))
            ->assertRedirect(route('filament.super-admin.pages.financial-overview', [
                'tableSearch' => 'Alpha',
                'provider' => 'paystack',
                'status' => 'paid',
            ]));

        $this->actingAs($admin, 'super_admin')
            ->get(route('super-admin.financial.index', ['renewal_window' => '7_days']))
            ->assertRedirect(route('filament.super-admin.pages.financial-overview', [
                'renewal_window' => '7_days',
            ]));
    }

    public function test_super_admin_can_delete_payment_records_and_subscribers(): void
    {
        $admin = SuperAdmin::query()->create([
            'name' => 'Delete Admin',
            'email' => 'delete-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);
        $plan = Plan::query()->create([
            'name' => 'Delete Test Plan',
            'slug' => 'delete-test-plan',
            'price_minor' => 10000,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'billing_interval_months' => 1,
            'features' => ['pos'],
            'is_active' => true,
        ]);
        $tenant = Tenant::query()->create([
            'id' => 'delete-test-'.Str::lower(Str::random(8)),
            'subscriber_code' => 'ES903',
            'name' => 'Delete Test Subscriber',
            'slug' => 'delete-test-subscriber-'.Str::lower(Str::random(8)),
            'email' => 'delete-subscriber@example.test',
            'status' => 'active',
        ]);
        $this->tenants[] = $tenant;
        $subscription = Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'provider' => 'internal',
            'starts_at' => now(),
        ]);
        $payment = Payment::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'provider' => 'manual',
            'provider_reference' => 'delete-test-payment',
            'amount_minor' => 10000,
            'currency' => 'GHS',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        Filament::setCurrentPanel(Filament::getPanel('super-admin'));
        $this->actingAs($admin, 'super_admin');

        Livewire::test(FinancialOverview::class)
            ->callTableAction('deletePayment', $payment)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id]);

        Livewire::test(\App\Filament\SuperAdmin\Pages\TenantDirectory::class)
            ->callTableAction('deleteSubscriber', $tenant)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    }
}
