<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Models\Tenant;
use App\Support\TenantPortalFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceDashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if ($user !== null && $user->role === 'admin' && $user->tenant_id !== null) {
            return redirect()->route('tenant.dashboard', [
                'tenant' => $user->tenant->slug,
            ], 302);
        }

        $selectedTenantId = $request->session()->get('workspace_tenant_id');
        $tenant = $user !== null ? $user->tenant : null;
        $tenant ??= $selectedTenantId ? Tenant::query()->find($selectedTenantId) : null;
        $tenant ??= Tenant::query()->first();
        if ($user === null && $selectedTenantId && $tenant?->status === 'suspended') {
            return redirect()->route('tenant.subscription.suspended', ['tenant' => $tenant->getTenantKey()]);
        }
        $subscription = $tenant?->subscriptions()->with('plan')->latest()->first();
        $features = TenantPortalFeatures::forSubscription($subscription);

        if ($user === null) {
            return Inertia::render('Dashboard', [
                'user' => null,
                'tenant' => $tenant === null ? null : [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                ],
                'subscription' => $subscription === null ? null : [
                    'plan' => $subscription->plan?->name,
                    'status' => $subscription->status,
                ],
                'access' => [
                    'onlineStore' => in_array('online_store', $features, true),
                    'pos' => in_array('pos', $features, true),
                    'restaurantFoodStore' => in_array('restaurant_foodstore', $features, true),
                    'foodstoreOnline' => in_array('foodstore_online', $features, true),
                ],
                'wallpaperUrl' => PlatformSetting::dashboardWallpaperUrl($request),
            ]);
        }

        return Inertia::render('Dashboard', [
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'tenant' => $tenant === null ? null : [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'subscription' => $subscription === null ? null : [
                'plan' => $subscription->plan?->name,
                'status' => $subscription->status,
            ],
            'access' => [
                'onlineStore' => in_array('online_store', $features, true),
                'pos' => in_array('pos', $features, true),
                'restaurantFoodStore' => in_array('restaurant_foodstore', $features, true),
            ],
            'wallpaperUrl' => PlatformSetting::dashboardWallpaperUrl($request),
        ]);
    }

    public function tenantDashboard(Request $request): Response
    {
        $user = $request->user();
        $tenant = tenant();
        abort_unless($user?->role === 'admin' && $tenant !== null && $user->tenant_id === $tenant->getTenantKey(), 403);
        if ($tenant->status === 'suspended') {
            return redirect()->route('tenant.subscription.suspended', ['tenant' => $tenant->getTenantKey()]);
        }

        $subscription = $tenant->subscriptions()->with('plan')->latest()->first();
        $features = TenantPortalFeatures::forSubscription($subscription);

        return Inertia::render('Dashboard', [
            'user' => [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'subscription' => $subscription === null ? null : [
                'plan' => $subscription->plan?->name,
                'status' => $subscription->status,
            ],
            'access' => [
                'onlineStore' => in_array('online_store', $features, true),
                'pos' => in_array('pos', $features, true),
                'restaurantFoodStore' => in_array('restaurant_foodstore', $features, true),
                'foodstoreOnline' => in_array('foodstore_online', $features, true),
            ],
            'wallpaperUrl' => PlatformSetting::dashboardWallpaperUrl($request),
            'adminDashboard' => true,
        ]);
    }
}
