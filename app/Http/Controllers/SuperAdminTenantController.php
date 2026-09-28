<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantPaystackSetting;
use App\Models\TenantSetting;
use App\Models\User;
use App\Support\TenantPortalFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminTenantController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $feature = $request->query('feature');
        $feature = in_array($feature, ['pos', 'online_store', 'restaurant_foodstore'], true) ? $feature : null;

        return Inertia::render('SuperAdmin/Tenants', [
            'tenants' => Tenant::query()->with('subscriptions.plan')
                ->when($search !== '', fn ($query) => $query->where(fn ($tenantQuery) => $tenantQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('subscriber_code', 'like', "%{$search}%")
                    ->orWhere('data->name', 'like', "%{$search}%")
                    ->orWhere('data->email', 'like', "%{$search}%")
                    ->orWhere('data->phone', 'like', "%{$search}%")
                    ->orWhere('data->slug', 'like', "%{$search}%")))
                ->when($feature !== null, fn ($query) => $query->whereHas('subscriptions', function ($subscriptionQuery) use ($feature): void {
                    $subscriptionQuery->whereIn('status', ['trialing', 'active'])
                        ->where(function ($accessQuery) use ($feature): void {
                            $accessQuery->whereJsonContains('metadata->features', $feature)
                                ->orWhere(function ($legacyQuery) use ($feature): void {
                                    $legacyQuery->where(function ($metadataQuery): void {
                                        $metadataQuery->whereNull('metadata')
                                            ->orWhereRaw("JSON_EXTRACT(metadata, '$.features') IS NULL");
                                    })->whereHas('plan', fn ($planQuery) => $planQuery->whereJsonContains('features', $feature));
                                });
                        });
                }))
                ->latest()->paginate(20)->withQueryString()->through(function (Tenant $tenant): array {
                    $subscription = $tenant->subscriptions->sortByDesc('created_at')->first();

                    return ['id' => $tenant->id, 'subscriber_code' => $tenant->subscriber_code, 'name' => $tenant->name, 'slug' => $tenant->slug, 'email' => $tenant->email, 'phone' => $tenant->phone, 'status' => $tenant->status, 'plan' => $subscription?->plan?->name, 'subscription_status' => $subscription?->status];
                }),
            'filters' => ['search' => $search, 'feature' => $feature],
            'enrol' => $request->boolean('enrol'),
            'status' => session('status'),
            'plans' => Plan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'features', 'price_minor', 'currency', 'billing_interval_months'])
                ->map(function (Plan $plan): array {
                    $billingLabel = match ($plan->billing_interval_months) {
                        1 => 'monthly',
                        3 => 'quarterly',
                        6 => 'every 6 months',
                        12 => 'yearly',
                        default => 'every '.$plan->billing_interval_months.' months',
                    };

                    return [
                        'id' => $plan->id,
                        'name' => $plan->name.' · '.strtoupper($plan->currency).' '.number_format($plan->price_minor / 100, 2).' / '.$billingLabel,
                        'features' => $plan->features,
                    ];
                }),
            'metrics' => [
                'subscriber_count' => Tenant::query()->count(),
                'active_count' => Tenant::query()->where('status', 'active')->count(),
                'suspended_count' => Tenant::query()->where('status', 'suspended')->count(),
                'active_subscriptions' => Subscription::query()->where('status', 'active')->count(),
                'workspace_users' => User::query()->whereNotNull('tenant_id')->count(),
            ],
        ]);
    }

    public function show(Tenant $tenant): Response
    {
        request()->session()->put('workspace_tenant_id', $tenant->id);
        $settings = $tenant->run(fn (): array => $this->storefrontSettings());
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();
        $team = $tenant->users()->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")->oldest()->get();

        return Inertia::render('SuperAdmin/Tenant', [
            'tenant' => $tenant->only(['id', 'subscriber_code', 'name', 'slug', 'email', 'phone', 'whatsapp_phone', 'status', 'created_at']),
            'storefrontLogoUrl' => \App\Models\PlatformSetting::storefrontLogoUrl(request(), $tenant),
            'hasCustomStorefrontLogo' => filled($tenant->data['storefront_logo'] ?? null),
            'paystackSettings' => $this->paystackSettingsForAdmin($tenant),
            'portalFeatures' => TenantPortalFeatures::forSubscription($subscription),
            'subscription' => $subscription === null ? null : [
                'id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $subscription->plan?->name,
                'amount_minor' => $subscription->amount_minor ?? $subscription->plan?->price_minor ?? 0,
                'status' => $subscription->status,
                'renews_at' => $subscription->renews_at?->toDateString(),
            ],
            'plans' => Plan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'price_minor', 'currency', 'billing_interval_months']),
            'team' => $team->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at?->toDateString(),
            ])->values(),
            'storefrontSettings' => $settings,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_phone' => [
                'nullable',
                'string',
                'max:40',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! filled($value)) {
                        return;
                    }

                    $digits = preg_replace('/\D+/', '', (string) $value);
                    if (! str_starts_with(trim((string) $value), '+') || strlen($digits) < 8 || strlen($digits) > 15) {
                        $fail('Enter a WhatsApp number in international format, including its country code.');
                    }
                },
            ],
            'status' => ['required', 'in:active,suspended'],
            'subscription_plan_id' => ['nullable', 'exists:plans,id'],
            'subscription_status' => ['nullable', 'in:trialing,active,past_due,disabled,cancelled'],
            'subscription_amount_ghs' => ['nullable', 'numeric', 'gt:0', 'max:1000000'],
            'catalogue_mode' => ['required', 'in:shared,separate_online'],
            'features' => ['required', 'array'],
            'features.*' => ['string', 'in:pos,online_store,restaurant_foodstore,foodstore_online,sales_expenses,audit_log,whatsapp_orders'],
            'storefront_store_name' => ['nullable', 'string', 'max:80'],
            'storefront_delivery_message' => ['nullable', 'string', 'max:160'],
            'storefront_hero_delivery_message' => ['nullable', 'string', 'max:160'],
            'storefront_customer_service_phone' => ['nullable', 'string', 'max:40'],
            'storefront_customer_service_email' => ['nullable', 'string', 'max:120'],
        ]);

        $tenant->update([
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?: null,
            'whatsapp_phone' => filled($data['whatsapp_phone'] ?? null) ? trim($data['whatsapp_phone']) : null,
            'status' => $data['status'],
        ]);
        $request->session()->put('workspace_tenant_id', $tenant->id);

        $subscription = $tenant->subscriptions()->latest()->first();
        if ($subscription !== null) {
            $subscription->update([
                'plan_id' => $data['subscription_plan_id'] ?? $subscription->plan_id,
                'status' => $data['subscription_status'] ?? $subscription->status,
                'amount_minor' => isset($data['subscription_amount_ghs'])
                    ? (int) round((float) $data['subscription_amount_ghs'] * 100)
                    : $subscription->amount_minor,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'features' => array_values(array_unique($data['features'])),
                ]),
            ]);
        }

        $tenant->run(function () use ($data): void {
            TenantSetting::query()->updateOrCreate(
                ['key' => 'catalogue_mode'],
                ['value' => $data['catalogue_mode']],
            );

            $existing = TenantSetting::query()->where('key', 'storefront_config')->value('value');
            $existing = is_array($existing) ? $existing : [];
            $service = is_array($existing['customer_service'] ?? null) ? $existing['customer_service'] : [];

            TenantSetting::query()->updateOrCreate(['key' => 'storefront_config'], ['value' => array_replace($existing, [
                'store_name' => filled($data['storefront_store_name'] ?? null) ? trim($data['storefront_store_name']) : null,
                'delivery_message' => filled($data['storefront_delivery_message'] ?? null) ? trim($data['storefront_delivery_message']) : 'Fast local delivery on every order',
                'hero_delivery_message' => filled($data['storefront_hero_delivery_message'] ?? null) ? trim($data['storefront_hero_delivery_message']) : 'Free local delivery on every order',
                'customer_service' => array_replace($service, [
                    'phone' => filled($data['storefront_customer_service_phone'] ?? null) ? trim($data['storefront_customer_service_phone']) : '+233 30 000 0000',
                    'email' => filled($data['storefront_customer_service_email'] ?? null) ? trim($data['storefront_customer_service_email']) : 'support@enablstore.test',
                ]),
            ])]);
        });

        return back()->with('status', 'Tenant settings updated.');
    }

    public function activateManually(Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();
        abort_if($subscription === null, 422, 'A subscription is required before this tenant can be activated.');

        if ($tenant->status === 'active' && $subscription->status === 'active') {
            return back()->with('status', 'Tenant is already active.');
        }

        $amountMinor = $subscription->amount_minor ?? $subscription->plan?->price_minor ?? 0;
        $renewalBase = $subscription->renews_at?->isFuture() ? $subscription->renews_at->copy() : now();
        $renewsAt = match ($subscription->plan?->billing_interval) {
            'weekly' => $renewalBase->addWeek(),
            'daily' => $renewalBase->addDay(),
            default => $renewalBase->addMonthsNoOverflow(max(1, (int) ($subscription->plan?->billing_interval_months ?? 1))),
        };

        DB::transaction(function () use ($tenant, $subscription, $amountMinor, $renewsAt): void {
            Payment::query()->create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'provider' => 'manual',
                'provider_reference' => 'manual-'.Str::uuid(),
                'amount_minor' => $amountMinor,
                'currency' => 'GHS',
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => ['source' => 'super_admin_manual_activation'],
            ]);

            $tenant->update(['status' => 'active']);
            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
                'renews_at' => $renewsAt,
                'ends_at' => null,
                'grace_ends_at' => null,
            ]);
        });

        return back()->with('status', 'Tenant manually activated and payment recorded.');
    }

    public function updatePaystackSettings(Request $request, Tenant $tenant): RedirectResponse
    {
        $settings = TenantPaystackSetting::query()->firstOrNew(['tenant_id' => $tenant->id]);
        $validated = $request->validate([
            'live_public_key' => ['nullable', 'string', 'max:255', 'starts_with:pk_live_'],
            'live_secret_key' => ['nullable', 'string', 'max:255', 'starts_with:sk_live_'],
            'test_public_key' => ['nullable', 'string', 'max:255', 'starts_with:pk_test_'],
            'test_secret_key' => ['nullable', 'string', 'max:255', 'starts_with:sk_test_'],
            'mode' => ['required', 'in:test,live'],
            'enabled' => ['required', 'boolean'],
        ]);

        $livePublicKey = $validated['live_public_key'] ?? $settings->public_key;
        $liveSecretKey = $validated['live_secret_key'] ?? $settings->secret_key;
        $testPublicKey = $validated['test_public_key'] ?? $settings->test_public_key;
        $testSecretKey = $validated['test_secret_key'] ?? $settings->test_secret_key;

        if ($validated['enabled']) {
            $activePublicKey = $validated['mode'] === 'test' ? $testPublicKey : $livePublicKey;
            $activeSecretKey = $validated['mode'] === 'test' ? $testSecretKey : $liveSecretKey;

            if (! filled($activePublicKey) || ! filled($activeSecretKey)) {
                return back()->withErrors([
                    $validated['mode'] === 'test' ? 'test_secret_key' : 'live_secret_key' => 'Enter both keys for the selected Paystack mode before enabling payments.',
                ]);
            }
        }

        $settings->public_key = $livePublicKey;
        $settings->secret_key = $liveSecretKey;
        $settings->test_public_key = $testPublicKey;
        $settings->test_secret_key = $testSecretKey;
        $settings->mode = $validated['mode'];
        $settings->enabled = (bool) $validated['enabled'];
        $settings->save();

        return back()->with('status', 'Tenant Paystack settings updated.');
    }

    /** @return array{mode: string, enabled: bool, live_public_key: ?string, live_configured: bool, test_public_key: ?string, test_configured: bool} */
    private function paystackSettingsForAdmin(Tenant $tenant): array
    {
        $settings = TenantPaystackSetting::query()->where('tenant_id', $tenant->id)->first();

        return [
            'mode' => $settings?->mode ?? 'live',
            'enabled' => (bool) $settings?->enabled,
            'live_public_key' => $settings?->public_key,
            'live_configured' => filled($settings?->public_key) && filled($settings?->secret_key),
            'test_public_key' => $settings?->test_public_key,
            'test_configured' => filled($settings?->test_public_key) && filled($settings?->test_secret_key),
        ];
    }

    /** @return array<string, string> */
    private function storefrontSettings(): array
    {
        $config = TenantSetting::query()->where('key', 'storefront_config')->value('value');
        $config = is_array($config) ? $config : [];
        $service = is_array($config['customer_service'] ?? null) ? $config['customer_service'] : [];

        return [
            'catalogue_mode' => TenantSetting::query()->where('key', 'catalogue_mode')->value('value') ?? 'shared',
            'storefront_store_name' => (string) ($config['store_name'] ?? ''),
            'storefront_delivery_message' => (string) ($config['delivery_message'] ?? 'Fast local delivery on every order'),
            'storefront_hero_delivery_message' => (string) ($config['hero_delivery_message'] ?? 'Free local delivery on every order'),
            'storefront_customer_service_phone' => (string) ($service['phone'] ?? '+233 30 000 0000'),
            'storefront_customer_service_email' => (string) ($service['email'] ?? 'support@enablstore.test'),
        ];
    }
}
