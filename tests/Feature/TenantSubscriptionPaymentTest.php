<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SuperAdmin;
use App\Models\Tenant;
use App\Models\TenantPaymentIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stancl\Tenancy\Tenancy;
use Tests\TestCase;

class TenantSubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    private ?Tenant $tenant = null;
    private ?Subscription $subscription = null;

    protected function tearDown(): void
    {
        app(Tenancy::class)->end();
        $this->tenant?->delete();

        parent::tearDown();
    }

    public function test_company_paystack_renewal_verifies_payment_then_activates_the_workspace(): void
    {
        [$tenant, $subscription] = $this->createSuspendedSubscription();
        config([
            'paystack.secret_key' => 'sk_test_company_account',
            'paystack.public_key' => 'pk_test_company_account',
            'paystack.base_url' => 'https://api.paystack.co',
        ]);
        Http::fake(function (HttpRequest $request) {
            $reference = basename((string) parse_url($request->url(), PHP_URL_PATH));

            return Http::response(['status' => true, 'data' => [
                'status' => 'success',
                'amount' => 12500,
                'currency' => 'GHS',
                'reference' => $reference,
            ]]);
        });

        $this->get(route('tenant.home', ['tenant' => $tenant->id]))
            ->assertRedirect(route('tenant.subscription.suspended', ['tenant' => $tenant->id], false));

        $checkoutResponse = $this->postJson(route('tenant.subscription.checkout', ['tenant' => $tenant->id]), [
            'email' => 'billing@example.test',
        ])->assertOk()->assertJsonPath('amount_minor', 12500)->assertJsonPath('public_key', 'pk_test_company_account');

        $intent = TenantPaymentIntent::query()
            ->where('tenant_id', $tenant->id)
            ->where('context', 'subscription_renewal')
            ->firstOrFail();

        $checkoutResponse->assertJsonPath('reference', $intent->reference);

        $this->get(route('tenant.subscription.callback', [
            'tenant' => $tenant->id,
            'reference' => $intent->reference,
        ]))->assertRedirect(route('tenant.subscription.suspended', ['tenant' => $tenant->id], false))
            ->assertSessionHas('subscription_status', 'Payment received. Your workspace service has been restored.');

        Http::assertSent(fn (HttpRequest $request): bool => str_contains($request->url(), '/transaction/verify/')
            && $request->hasHeader('Authorization', 'Bearer sk_test_company_account'));

        $this->assertSame('active', DB::connection('sqlite')->table('tenants')->where('id', $tenant->id)->value('status'));
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertSame('completed', $intent->fresh()->status);
        $this->assertTrue(DB::connection('sqlite')->table('payments')->where([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'provider' => 'paystack',
            'provider_reference' => $intent->reference,
            'amount_minor' => 12500,
            'status' => 'paid',
        ])->exists());
    }

    public function test_superadmin_can_manually_activate_after_offline_payment(): void
    {
        [$tenant, $subscription] = $this->createSuspendedSubscription();
        $admin = SuperAdmin::query()->create([
            'name' => 'Test Super Admin',
            'email' => 'manual-admin@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->actingAs($admin, 'super_admin')
            ->patch(route('super-admin.tenants.activate', ['tenant' => $tenant->id]))
            ->assertRedirect();

        $this->assertSame('active', $tenant->fresh()->status);
        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'provider' => 'manual',
            'amount_minor' => 12500,
            'status' => 'paid',
        ]);
    }

    /** @return array{Tenant, Subscription} */
    private function createSuspendedSubscription(): array
    {
        $id = 'suspended-'.Str::lower(Str::random(8));
        $this->tenant = Tenant::query()->create([
            'id' => $id,
            'subscriber_code' => 'ES906',
            'name' => 'Suspended Subscriber',
            'slug' => $id,
            'email' => 'subscriber@example.test',
            'status' => 'suspended',
            'data' => ['subscriber_code' => 'ES906'],
        ]);
        app(Tenancy::class)->end();

        $plan = Plan::query()->create([
            'name' => 'Monthly test plan',
            'slug' => $id.'-plan',
            'price_minor' => 10000,
            'currency' => 'GHS',
            'billing_interval' => 'monthly',
            'features' => ['online_store', 'pos', 'restaurant_foodstore'],
            'is_active' => true,
        ]);
        $this->subscription = Subscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
            'amount_minor' => 12500,
            'provider' => 'internal',
            'status' => 'past_due',
            'starts_at' => now()->subMonth(),
            'renews_at' => now()->subDay(),
            'metadata' => ['features' => ['online_store', 'pos', 'restaurant_foodstore']],
        ]);

        return [$this->tenant, $this->subscription];
    }
}
