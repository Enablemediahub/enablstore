<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Subscription;

class TenantPortalFeatures
{
    public const AVAILABLE = ['pos', 'online_store', 'restaurant_foodstore', 'foodstore_online'];

    /** @return list<string> */
    public static function forSubscription(?Subscription $subscription): array
    {
        if ($subscription === null) {
            return [];
        }

        $configured = $subscription->metadata['features'] ?? $subscription->plan?->features ?? [];

        if (! is_array($configured)) {
            return [];
        }

        return array_values(array_intersect(self::AVAILABLE, $configured));
    }
}