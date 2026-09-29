<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Str;

class SubscriptionPlanManager
{
    /** @param array<string, mixed> $data */
    public function create(array $data): Plan
    {
        $slug = Str::slug($data['name']);
        $baseSlug = $slug !== '' ? $slug : 'subscription';
        $suffix = 2;

        while (Plan::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return Plan::query()->create($this->attributes($data) + ['slug' => $slug]);
    }

    /** @param array<string, mixed> $data */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($this->attributes($data));

        return $plan;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $months = (int) $data['billing_interval_months'];

        return [
            'name' => trim($data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'price_minor' => (int) round((float) $data['price_ghs'] * 100),
            'currency' => 'GHS',
            'billing_interval' => match ($months) {
                1 => 'monthly',
                3 => 'quarterly',
                6 => 'semiannual',
                12 => 'yearly',
                default => "every_{$months}_months",
            },
            'billing_interval_months' => $months,
            'features' => array_values(array_unique($data['features'])),
            'is_active' => (bool) $data['is_active'],
        ];
    }
}
