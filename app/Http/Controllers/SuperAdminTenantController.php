<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminTenantController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $feature = $request->query('feature');
        $feature = in_array($feature, ['pos', 'online_store'], true) ? $feature : null;

        return Inertia::render('SuperAdmin/Tenants', [
            'tenants' => Tenant::query()->with('subscriptions.plan')
                ->when($search !== '', fn ($query) => $query->where(fn ($tenantQuery) => $tenantQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
                ->when($feature !== null, fn ($query) => $query->whereHas('subscriptions.plan', fn ($planQuery) => $planQuery->whereJsonContains('features', $feature)))
                ->latest()->paginate(20)->withQueryString()->through(function (Tenant $tenant): array {
                    $subscription = $tenant->subscriptions->sortByDesc('created_at')->first();

                    return ['id' => $tenant->id, 'subscriber_code' => $tenant->subscriber_code, 'name' => $tenant->name, 'slug' => $tenant->slug, 'email' => $tenant->email, 'status' => $tenant->status, 'plan' => $subscription?->plan?->name, 'subscription_status' => $subscription?->status];
                }),
            'filters' => ['search' => $search, 'feature' => $feature],
            'plans' => Plan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
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
        $settings = $tenant->run(fn (): array => $this->storefrontSettings());
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();
        $team = $tenant->users()->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")->oldest()->get();

        return Inertia::render('SuperAdmin/Tenant', [
            'tenant' => $tenant->only(['id', 'subscriber_code', 'name', 'slug', 'email', 'phone', 'status', 'created_at']),
            'subscription' => $subscription === null ? null : [
                'id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'plan_name' => $subscription->plan?->name,
                'status' => $subscription->status,
                'renews_at' => $subscription->renews_at?->toDateString(),
            ],
            'plans' => Plan::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
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
            'status' => ['required', 'in:active,suspended'],
            'subscription_plan_id' => ['nullable', 'exists:plans,id'],
            'subscription_status' => ['nullable', 'in:trialing,active,past_due,disabled,cancelled'],
            'catalogue_mode' => ['required', 'in:shared,separate_online'],
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
            'status' => $data['status'],
        ]);

        $subscription = $tenant->subscriptions()->latest()->first();
        if ($subscription !== null) {
            $subscription->update([
                'plan_id' => $data['subscription_plan_id'] ?? $subscription->plan_id,
                'status' => $data['subscription_status'] ?? $subscription->status,
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
