<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantManagementService;
use App\Support\TenantPortalFeatures;
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
        $management = app(TenantManagementService::class);
        $settings = $management->storefrontSettings($tenant);
        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();
        $team = $tenant->users()->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")->oldest()->get();

        return Inertia::render('SuperAdmin/Tenant', [
            'tenant' => array_merge($tenant->only(['id', 'subscriber_code', 'name', 'slug', 'email', 'phone', 'whatsapp_phone', 'status', 'created_at']), [
                'team_management_enabled' => $tenant->teamManagementEnabled(),
            ]),
            'storefrontLogoUrl' => PlatformSetting::storefrontLogoUrl(request(), $tenant),
            'hasCustomStorefrontLogo' => filled($tenant->data['storefront_logo'] ?? null),
            'paystackSettings' => $management->paystackSettings($tenant),
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
        $data = $request->validate(TenantManagementService::updateRules());
        app(TenantManagementService::class)->update($tenant, $data);
        $request->session()->put('workspace_tenant_id', $tenant->id);

        return back()->with('status', 'Tenant settings updated.');
    }

    public function activateManually(Tenant $tenant): RedirectResponse
    {
        $subscription = $tenant->subscriptions()->latest()->first();
        abort_if($subscription === null, 422, 'A subscription is required before this tenant can be activated.');

        if ($tenant->status === 'active' && $subscription->status === 'active') {
            return back()->with('status', 'Tenant is already active.');
        }
        app(TenantManagementService::class)->activateManually($tenant);

        return back()->with('status', 'Tenant manually activated and payment recorded.');
    }

    public function updatePaystackSettings(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate(TenantManagementService::paystackRules());
        app(TenantManagementService::class)->updatePaystackSettings($tenant, $validated);

        return back()->with('status', 'Tenant Paystack settings updated.');
    }
}
