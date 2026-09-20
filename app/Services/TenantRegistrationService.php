<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\RegisterTenantRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantRegistrationService
{
    /**
     * Register a tenant and provision its initial trial subscription.
     *
     * @return array{tenant: Tenant, subscription: Subscription}
     */
    public function register(RegisterTenantRequest $request): array
    {
        return DB::transaction(function () use ($request): array {
            $tenant = Tenant::create([
                'id' => $request->string('slug')->toString(),
                'name' => $request->string('business_name')->toString(),
                'slug' => $request->string('slug')->toString(),
                'email' => $request->string('email')->toString(),
                'phone' => $request->input('phone'),
                'status' => 'trial',
                'data' => [
                    'owner_name' => $request->string('owner_name')->toString(),
                ],
            ]);

            $plan = Plan::query()->where('slug', 'starter')->where('is_active', true)->firstOrFail();

            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'provider' => 'internal',
                'status' => 'trialing',
                'starts_at' => now(),
                'renews_at' => now()->addDays(14),
            ]);

            return compact('tenant', 'subscription');
        });
    }
}