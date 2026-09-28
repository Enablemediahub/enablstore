<?php

declare(strict_types=1);

use App\Http\Controllers\OfflineSalesSyncController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\TenantAnalyticsController;
use App\Http\Controllers\TenantProductController;
use App\Http\Controllers\TenantCategoryController;
use App\Http\Controllers\TenantSettingsController;
use App\Http\Controllers\TenantSupplierController;
use App\Http\Controllers\TenantReportsController;
use App\Http\Controllers\TenantSalesExpensesController;
use App\Http\Controllers\TenantAuditController;
use App\Http\Controllers\TenantTeamController;
use App\Http\Controllers\TenantPosAccessController;
use App\Http\Controllers\TenantPaymentController;
use App\Http\Controllers\TenantSubscriptionController;
use App\Http\Controllers\RestaurantFoodStoreController;
use App\Http\Controllers\WorkspaceDashboardController;
use App\Http\Middleware\EnsureTenantAdmin;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByPath::class,
])->group(function () {
    Route::get('/billing/{tenant}/suspended', [TenantSubscriptionController::class, 'suspended'])
        ->name('tenant.subscription.suspended');
    Route::post('/billing/{tenant}/checkout', [TenantSubscriptionController::class, 'checkout'])
        ->name('tenant.subscription.checkout');
    Route::get('/billing/{tenant}/callback', [TenantSubscriptionController::class, 'callback'])
        ->name('tenant.subscription.callback');
    Route::get('/{tenant}/dashboard', [WorkspaceDashboardController::class, 'tenantDashboard'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.dashboard');
    Route::get('/foodstore/{tenant}', [RestaurantFoodStoreController::class, 'index'])
        ->name('tenant.foodstore.index');
    Route::get('/foodstore-online/{tenant}', [RestaurantFoodStoreController::class, 'online'])
        ->middleware(['feature:foodstore_online'])
        ->name('tenant.foodstore.online');
    Route::post('/foodstore-online/{tenant}/orders', [RestaurantFoodStoreController::class, 'storeOnlineOrder'])
        ->middleware(['feature:foodstore_online'])
        ->name('tenant.foodstore.online.orders.store');
    Route::get('/foodstore/{tenant}/menu', [RestaurantFoodStoreController::class, 'menu'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:restaurant_foodstore'])
        ->name('tenant.foodstore.menu.index');
    Route::post('/foodstore/{tenant}/menu', [RestaurantFoodStoreController::class, 'storeMenuItem'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:restaurant_foodstore'])
        ->name('tenant.foodstore.menu.store');
    Route::patch('/foodstore/{tenant}/menu/{menuItem}', [RestaurantFoodStoreController::class, 'updateMenuItem'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:restaurant_foodstore'])
        ->name('tenant.foodstore.menu.update');
    Route::delete('/foodstore/{tenant}/menu/{menuItem}', [RestaurantFoodStoreController::class, 'destroyMenuItem'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:restaurant_foodstore'])
        ->name('tenant.foodstore.menu.destroy');
    Route::post('/foodstore/{tenant}/sales', [RestaurantFoodStoreController::class, 'storeSale'])
        ->middleware(['feature:restaurant_foodstore', \App\Http\Middleware\EnsureFoodStoreAccess::class])
        ->name('tenant.foodstore.sales.store');
    Route::post('/foodstore/{tenant}/orders', [RestaurantFoodStoreController::class, 'storeOrder'])
        ->middleware(['feature:restaurant_foodstore', \App\Http\Middleware\EnsureFoodStoreAccess::class])
        ->name('tenant.foodstore.orders.store');
    Route::get('/foodstore/{tenant}/orders', [RestaurantFoodStoreController::class, 'orders'])
        ->middleware(['feature:restaurant_foodstore', \App\Http\Middleware\EnsureFoodStoreAccess::class])
        ->name('tenant.foodstore.orders.index');
    Route::patch('/foodstore/{tenant}/orders/{order}/status', [RestaurantFoodStoreController::class, 'updateOrderStatus'])
        ->middleware(['feature:restaurant_foodstore', \App\Http\Middleware\EnsureFoodStoreAccess::class])
        ->name('tenant.foodstore.orders.status');

    Route::get('/onlinestore/{tenant}', [StorefrontController::class, 'index'])
        ->middleware(['feature:online_store'])
        ->name('tenant.home');
    Route::post('/onlinestore/{tenant}/checkout', [TenantPaymentController::class, 'storefrontCheckout'])
        ->middleware(['feature:online_store'])
        ->name('tenant.storefront.checkout');
    Route::get('/onlinestore/{tenant}/payment/callback', [TenantPaymentController::class, 'storefrontCallback'])
        ->middleware(['feature:online_store'])
        ->name('tenant.storefront.payment.callback');
    Route::get('/onlinestore/{tenant}/storefront', [StorefrontController::class, 'index'])
        ->middleware(['feature:online_store'])
        ->name('tenant.storefront');

    Route::get('/client/{tenant}/products', [TenantProductController::class, 'index'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.products.index');
    Route::get('/client/{tenant}/categories', [TenantCategoryController::class, 'index'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:pos,restaurant_foodstore'])
        ->name('tenant.categories.index');
    Route::post('/client/{tenant}/categories', [TenantCategoryController::class, 'store'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:pos,restaurant_foodstore'])
        ->name('tenant.categories.store');
    Route::delete('/client/{tenant}/categories/{category}', [TenantCategoryController::class, 'destroy'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:pos,restaurant_foodstore'])
        ->name('tenant.categories.destroy');
    Route::get('/client/{tenant}/settings', [TenantSettingsController::class, 'index'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.settings.index');
    Route::patch('/client/{tenant}/settings', [TenantSettingsController::class, 'update'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.settings.update');
    Route::post('/client/{tenant}/settings/hero-image', [TenantSettingsController::class, 'updateHeroImage'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.settings.hero-image');
    Route::delete('/client/{tenant}/settings/hero-image', [TenantSettingsController::class, 'destroyHeroImage'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.settings.hero-image.destroy');
    Route::get('/client/{tenant}/suppliers', [TenantSupplierController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.suppliers.index');
    Route::post('/client/{tenant}/suppliers', [TenantSupplierController::class, 'store'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.suppliers.store');
    Route::post('/client/{tenant}/suppliers/receive', [TenantSupplierController::class, 'receive'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.suppliers.receive');
    Route::get('/client/{tenant}/reports', [TenantReportsController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.reports');
    Route::get('/client/{tenant}/sales-expenses', [TenantSalesExpensesController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:sales_expenses'])->name('tenant.sales-expenses.index');
    Route::post('/client/{tenant}/sales-expenses', [TenantSalesExpensesController::class, 'storeExpense'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:sales_expenses'])->name('tenant.sales-expenses.store');
    Route::get('/client/{tenant}/audit', [TenantAuditController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class, 'feature:audit_log'])->name('tenant.audit');
    Route::get('/client/{tenant}/team', [TenantTeamController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.team.index');
    Route::post('/client/{tenant}/team', [TenantTeamController::class, 'store'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.team.store');
    Route::patch('/client/{tenant}/team/{user}', [TenantTeamController::class, 'update'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.team.update');
    Route::get('/onlinestore/{tenant}/media/{path}', [TenantProductController::class, 'media'])
        ->where('path', '.*')
        ->middleware([])
        ->name('tenant.media');
    Route::get('/client/{tenant}/products/barcode-lookup/{barcode}', [TenantProductController::class, 'barcodeLookup'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.products.barcode-lookup');
    Route::post('/client/{tenant}/products', [TenantProductController::class, 'store'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.products.store');
    Route::patch('/client/{tenant}/products/{product}', [TenantProductController::class, 'update'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.products.update');
    Route::delete('/client/{tenant}/products/{product}', [TenantProductController::class, 'destroy'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.products.destroy');
    Route::get('/pos/{tenant}', [PosController::class, 'index'])
        ->middleware(['feature:pos'])
        ->name('tenant.pos');
    Route::get('/pos/{tenant}/manifest.json', [PosController::class, 'manifest'])
        ->middleware(['feature:pos'])
        ->name('tenant.pos.manifest');
    Route::post('/pos/{tenant}/unlock', [TenantPosAccessController::class, 'unlock'])
        ->middleware([])
        ->name('tenant.pos.unlock');
    Route::post('/pos/{tenant}/logout', [TenantPosAccessController::class, 'logout'])
        ->middleware([])
        ->name('tenant.pos.logout');
    Route::post('/pos/{tenant}/checkout', [TenantPaymentController::class, 'posCheckout'])
        ->middleware(['feature:pos', \App\Http\Middleware\EnsurePosAccess::class])
        ->name('tenant.pos.checkout');
    Route::get('/pos/{tenant}/payment/callback', [TenantPaymentController::class, 'callback'])
        ->middleware(['feature:pos', \App\Http\Middleware\EnsurePosAccess::class])
        ->name('tenant.payment.callback');
    Route::post('/pos/{tenant}/sync', OfflineSalesSyncController::class)
        ->middleware(['feature:pos', \App\Http\Middleware\EnsurePosAccess::class])
        ->name('tenant.pos.sync');
    Route::get('/client/{tenant}/analytics', TenantAnalyticsController::class)
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.analytics');

    // Keep existing bookmarks working while all new links use the clean public URLs.
    Route::get('/client/{tenant}', fn () => redirect()->route('tenant.home', ['tenant' => tenant()->getTenantKey()]));
    Route::get('/client/{tenant}/storefront', fn () => redirect()->route('tenant.home', ['tenant' => tenant()->getTenantKey()]));
    Route::get('/client/{tenant}/pos', fn () => redirect()->route('tenant.pos', ['tenant' => tenant()->getTenantKey()]));
    Route::get('/client/{tenant}/pos/manifest.json', fn () => redirect()->route('tenant.pos.manifest', ['tenant' => tenant()->getTenantKey()]));
    Route::get('/client/{tenant}/media/{path}', fn () => redirect()->route('tenant.media', ['tenant' => tenant()->getTenantKey(), 'path' => request()->route('path')]))
        ->where('path', '.*');
});
