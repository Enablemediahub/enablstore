<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
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
            abort(402, 'Your current plan does not include this feature.');
        }

        return $next($request);
    }
}
