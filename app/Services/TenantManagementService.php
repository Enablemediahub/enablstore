<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantPaystackSetting;
use App\Models\TenantSetting;
use App\Support\PublicAssetPublisher;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantManagementService
{
    /** @return array<string, mixed> */
    public static function updateRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_phone' => [
                'nullable',
                'string',
                'max:40',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! filled($value)) {
                        return;
                    }

                    $digits = preg_replace('/\D+/', '', (string) $value);
                    if (! str_starts_with(trim((string) $value), '+') || strlen($digits) < 8 || strlen($digits) > 15) {
                        $fail('Enter a WhatsApp number in international format, including its country code.');
                    }
                },
            ],
            'status' => ['required', 'in:trial,active,suspended'],
            'team_management_enabled' => ['sometimes', 'boolean'],
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
            'storefront_logo' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, mixed> */
    public static function paystackRules(): array
    {
        return [
            'live_public_key' => ['nullable', 'string', 'max:255', 'starts_with:pk_live_'],
            'live_secret_key' => ['nullable', 'string', 'max:255', 'starts_with:sk_live_'],
            'test_public_key' => ['nullable', 'string', 'max:255', 'starts_with:pk_test_'],
            'test_secret_key' => ['nullable', 'string', 'max:255', 'starts_with:sk_test_'],
            'mode' => ['required', 'in:test,live'],
            'enabled' => ['required', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $data */
    public function update(Tenant $tenant, array $data): void
    {
        $tenantAttributes = [
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?: null,
            'whatsapp_phone' => filled($data['whatsapp_phone'] ?? null) ? trim($data['whatsapp_phone']) : null,
            'status' => $data['status'],
        ];
        if (array_key_exists('team_management_enabled', $data)) {
            $tenantAttributes['team_management_enabled'] = (bool) $data['team_management_enabled'];
        }
        $tenant->update($tenantAttributes);

        if (filled($data['storefront_logo'] ?? null)) {
            $this->saveStorefrontLogo($tenant, $data['storefront_logo']);
        }

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
    }

    public function activateManually(Tenant $tenant): void
    {
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();
        abort_if($subscription === null, 422, 'A subscription is required before this tenant can be activated.');

        if ($tenant->status === 'active' && $subscription->status === 'active') {
            return;
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
    }

    /** @param array<string, mixed> $data */
    public function updatePaystackSettings(Tenant $tenant, array $data): void
    {
        $settings = TenantPaystackSetting::query()->firstOrNew(['tenant_id' => $tenant->id]);
        $livePublicKey = $data['live_public_key'] ?? $settings->public_key;
        $liveSecretKey = $data['live_secret_key'] ?? $settings->secret_key;
        $testPublicKey = $data['test_public_key'] ?? $settings->test_public_key;
        $testSecretKey = $data['test_secret_key'] ?? $settings->test_secret_key;

        if ($data['enabled']) {
            $activePublicKey = $data['mode'] === 'test' ? $testPublicKey : $livePublicKey;
            $activeSecretKey = $data['mode'] === 'test' ? $testSecretKey : $liveSecretKey;

            if (! filled($activePublicKey) || ! filled($activeSecretKey)) {
                $secretField = $data['mode'] === 'test' ? 'test_secret_key' : 'live_secret_key';
                throw ValidationException::withMessages([
                    $secretField => 'Enter both keys for the selected Paystack mode before enabling payments.',
                ]);
            }
        }

        $settings->public_key = $livePublicKey;
        $settings->secret_key = $liveSecretKey;
        $settings->test_public_key = $testPublicKey;
        $settings->test_secret_key = $testSecretKey;
        $settings->mode = $data['mode'];
        $settings->enabled = (bool) $data['enabled'];
        $settings->save();
    }

    /** @return array<string, mixed> */
    public function storefrontSettings(Tenant $tenant): array
    {
        return $tenant->run(function (): array {
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
        });
    }

    /** @return array{mode: string, enabled: bool, live_public_key: ?string, live_configured: bool, test_public_key: ?string, test_configured: bool} */
    public function paystackSettings(Tenant $tenant): array
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

    private function saveStorefrontLogo(Tenant $tenant, string $path): void
    {
        $data = is_array($tenant->data) ? $tenant->data : [];

        if (filled($data['storefront_logo'] ?? null)) {
            throw ValidationException::withMessages([
                'data.storefront_logo' => 'This subscriber logo is permanent and cannot be changed.',
            ]);
        }

        PublicAssetPublisher::publish($path);
        $data['storefront_logo'] = $path;
        $tenant->update(['data' => $data]);
    }
}
