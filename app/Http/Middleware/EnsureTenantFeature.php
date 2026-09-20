<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = tenant();
        $subscription = $tenant?->subscriptions()->with('plan')->latest()->first();
        $plan = $subscription?->plan;
        $features = $plan === null ? [] : $plan->features;
        $activeStatuses = ['trialing', 'active'];

        if ($subscription === null || ! in_array($subscription->status, $activeStatuses, true) || ! in_array($feature, $features, true)) {
            abort(402, 'Your current plan does not include this feature.');
        }

        return $next($request);
    }
}
