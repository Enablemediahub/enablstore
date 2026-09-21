<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\PlatformSetting;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'metrics' => [
                'tenant_count' => Tenant::query()->count(),
                'active_subscriptions' => Subscription::query()->where('status', 'active')->count(),
                'paid_revenue_minor' => Payment::query()->where('status', 'paid')->sum('amount_minor'),
            ],
            'tenants' => Tenant::query()->with('subscriptions.plan')->latest()->paginate(20),
            'loginWallpaperUrl' => PlatformSetting::loginWallpaperUrl(request()),
            'storefrontLogoUrl' => PlatformSetting::storefrontLogoUrl(request()),
            'defaultStorefrontLogoUrl' => request()->getSchemeAndHttpHost().'/images/storefront/enablstore-logo.png',
            'hasCustomStorefrontLogo' => filled(PlatformSetting::value('storefront_logo')),
            'status' => session('status'),
            'catalogueModeDefault' => PlatformSetting::value('catalogue_mode_default', 'shared'),
        ]);
    }
}
