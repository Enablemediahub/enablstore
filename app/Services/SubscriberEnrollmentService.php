<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriberEnrollmentService
{
    /** @param array<string, mixed> $data */
    public function create(array $data): Tenant
    {
        $slug = $this->uniqueTenantSlug($data['business_name']);
        $subscriberCode = $this->nextSubscriberCode();
        $username = $subscriberCode.'-'.strtolower($data['username']);

        if (User::query()->where('username', $username)->exists()) {
            throw ValidationException::withMessages([
                'username' => 'This username is already in use for the generated subscriber code.',
            ]);
        }

        $plan = Plan::query()->where('is_active', true)->findOrFail((int) $data['plan_id']);
        $tenant = Tenant::query()->create([
            'id' => $slug,
            'subscriber_code' => $subscriberCode,
            'name' => $data['business_name'],
            'slug' => $slug,
            'email' => $data['email'] ?? null,
            'status' => 'active',
        ]);

        DB::transaction(function () use ($data, $tenant, $plan, $username): void {
            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $data['plan_id'],
                'amount_minor' => $plan->price_minor,
                'provider' => 'internal',
                'status' => 'active',
                'starts_at' => now(),
                'renews_at' => now()->addMonthsNoOverflow(max(1, $plan->billing_interval_months)),
                'metadata' => ['features' => array_values(array_unique($data['features']))],
            ]);

            Payment::query()->create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'provider' => 'manual',
                'provider_reference' => 'manual-'.Str::uuid(),
                'amount_minor' => $plan->price_minor,
                'currency' => $plan->currency,
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => ['source' => 'super_admin_manual_enrollment'],
            ]);

            User::query()->create([
                'name' => $data['name'],
                'username' => $username,
                'email' => $data['email'] ?? null,
                'tenant_id' => $tenant->id,
                'password' => $data['password'],
                'role' => 'admin',
            ]);
        });

        return $tenant;
    }

    private function uniqueTenantSlug(string $businessName): string
    {
        $base = Str::slug($businessName);
        $base = Str::limit($base !== '' ? $base : 'subscriber', 52, '');
        $slug = $base;
        $suffix = 2;

        while (Tenant::query()->whereKey($slug)->exists()) {
            $slug = Str::limit($base, 52 - strlen((string) $suffix), '').'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function nextSubscriberCode(): string
    {
        $number = Tenant::query()
            ->whereNotNull('subscriber_code')
            ->get(['subscriber_code'])
            ->map(fn (Tenant $tenant): int => (int) substr((string) $tenant->subscriber_code, 2))
            ->max() + 1;

        return 'ES'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}
