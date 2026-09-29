<?php

namespace App\Providers\Filament;

use App\Filament\SuperAdmin\Pages\AdminAccounts;
use App\Filament\SuperAdmin\Pages\Branding;
use App\Filament\SuperAdmin\Pages\FinancialOverview;
use App\Filament\SuperAdmin\Pages\SubscriptionPlans;
use App\Filament\SuperAdmin\Pages\TenantDirectory;
use App\Filament\SuperAdmin\Pages\TenantManagement;
use App\Filament\SuperAdmin\Widgets\PlatformOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('super-admin')
            ->path('super-admin')
            ->login()
            ->authGuard('super_admin')
            ->brandName('Enablstore Super Admin')
            ->brandLogo(asset('images/storefront/enablstore-logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/storefront/enablstore-logo.png'))
            ->colors([
                'primary' => Color::Red,
            ])
            ->navigationGroups([
                'Management',
                'Platform tools',
            ])
            ->pages([
                Pages\Dashboard::class,
                TenantDirectory::class,
                TenantManagement::class,
                SubscriptionPlans::class,
                FinancialOverview::class,
                Branding::class,
                AdminAccounts::class,
            ])
            ->widgets([
                PlatformOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
