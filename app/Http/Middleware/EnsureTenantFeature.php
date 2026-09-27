<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Support\TenantPortalFeatures;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantFeature
{
    public function handle(Request $request, Closure $next, string ...$requiredFeatures): Response
    {
        $tenant = tenant();
        if ($tenant?->status === 'suspended') {
            if ($request->isMethod('GET')) {
                return redirect()->route('tenant.subscription.suspended', ['tenant' => $tenant->getTenantKey()]);
            }

            abort(402, 'This tenant is suspended. Complete the subscription payment to continue.');
        }

        $subscription = $tenant?->subscriptions()->with('plan')->latest()->first();
        $features = TenantPortalFeatures::forSubscription($subscription);
        $activeStatuses = ['trialing', 'active'];
        $hasFeature = array_intersect($requiredFeatures, $features) !== [];

        if ($subscription === null || ! in_array($subscription->status, $activeStatuses, true) || ! $hasFeature) {
            if ($request->isMethod('GET')) {
                $featureName = match (true) {
                    in_array('pos', $requiredFeatures, true) && in_array('restaurant_foodstore', $requiredFeatures, true) => 'POS and FoodStore features',
                    in_array('online_store', $requiredFeatures, true) => 'Online Store',
                    in_array('foodstore_online', $requiredFeatures, true) => 'FoodStore Online',
                    in_array('restaurant_foodstore', $requiredFeatures, true) => 'FoodStore',
                    in_array('pos', $requiredFeatures, true) => 'Point of Sale',
                    default => 'this portal',
                };

                return Inertia::render('Tenant/FeatureDenied', [
                    'tenantName' => (string) ($tenant?->name ?? 'your workspace'),
                    'featureName' => $featureName,
                    'dashboardUrl' => route('dashboard'),
                ])->toResponse($request);
            }

            abort(402, 'Your current plan does not include this feature.');
        }

        return $next($request);
    }
}
