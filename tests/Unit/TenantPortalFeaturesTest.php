<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\Subscription;
use App\Support\TenantPortalFeatures;
use PHPUnit\Framework\TestCase;

class TenantPortalFeaturesTest extends TestCase
{
    public function test_plan_features_are_used_when_no_subscriber_override_exists(): void
    {
        $subscription = new Subscription;
        $subscription->setRelation('plan', new Plan(['features' => ['pos', 'online_store', 'audit_log', 'whatsapp_orders']]));

        self::assertSame(['pos', 'online_store', 'audit_log', 'whatsapp_orders'], TenantPortalFeatures::forSubscription($subscription));
    }

    public function test_subscriber_portal_selection_overrides_shared_plan_features(): void
    {
        $subscription = new Subscription;
        $subscription->metadata = ['features' => ['restaurant_foodstore', 'audit_log', 'whatsapp_orders']];
        $subscription->setRelation('plan', new Plan(['features' => ['pos', 'online_store']]));

        self::assertSame(['restaurant_foodstore', 'audit_log', 'whatsapp_orders'], TenantPortalFeatures::forSubscription($subscription));
    }
}