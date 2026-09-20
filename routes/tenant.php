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
use App\Http\Controllers\TenantAuditController;
use App\Http\Controllers\TenantTeamController;
use App\Http\Controllers\TenantPosAccessController;
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
    Route::get('/client/{tenant}', [StorefrontController::class, 'index'])
        ->middleware(['feature:online_store'])
        ->name('tenant.home');

    Route::get('/client/{tenant}/products', [TenantProductController::class, 'index'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.products.index');
    Route::get('/client/{tenant}/categories', [TenantCategoryController::class, 'index'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.categories.index');
    Route::post('/client/{tenant}/categories', [TenantCategoryController::class, 'store'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.categories.store');
    Route::get('/client/{tenant}/settings', [TenantSettingsController::class, 'index'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.settings.index');
    Route::patch('/client/{tenant}/settings', [TenantSettingsController::class, 'update'])
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.settings.update');
    Route::get('/client/{tenant}/suppliers', [TenantSupplierController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.suppliers.index');
    Route::post('/client/{tenant}/suppliers', [TenantSupplierController::class, 'store'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.suppliers.store');
    Route::post('/client/{tenant}/suppliers/receive', [TenantSupplierController::class, 'receive'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.suppliers.receive');
    Route::get('/client/{tenant}/reports', [TenantReportsController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.reports');
    Route::get('/client/{tenant}/audit', [TenantAuditController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.audit');
    Route::get('/client/{tenant}/team', [TenantTeamController::class, 'index'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.team.index');
    Route::post('/client/{tenant}/team', [TenantTeamController::class, 'store'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.team.store');
    Route::patch('/client/{tenant}/team/{user}', [TenantTeamController::class, 'update'])->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])->name('tenant.team.update');
    Route::get('/client/{tenant}/media/{path}', [TenantProductController::class, 'media'])
        ->where('path', '.*')
        ->middleware(['auth', 'tenant.access'])
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
    Route::get('/client/{tenant}/pos', [PosController::class, 'index'])
        ->middleware(['feature:pos'])
        ->name('tenant.pos');
    Route::get('/client/{tenant}/pos/manifest.json', [PosController::class, 'manifest'])
        ->middleware(['feature:pos'])
        ->name('tenant.pos.manifest');
    Route::post('/client/{tenant}/pos/unlock', [TenantPosAccessController::class, 'unlock'])
        ->middleware([])
        ->name('tenant.pos.unlock');
    Route::post('/client/{tenant}/pos/logout', [TenantPosAccessController::class, 'logout'])
        ->middleware([])
        ->name('tenant.pos.logout');
    Route::post('/client/{tenant}/pos/checkout', [PosController::class, 'checkout'])
        ->middleware(['feature:pos', \App\Http\Middleware\EnsurePosAccess::class])
        ->name('tenant.pos.checkout');
    Route::post('/client/{tenant}/pos/sync', OfflineSalesSyncController::class)
        ->middleware(['feature:pos', \App\Http\Middleware\EnsurePosAccess::class])
        ->name('tenant.pos.sync');
    Route::get('/client/{tenant}/analytics', TenantAnalyticsController::class)
        ->middleware(['auth', 'tenant.access', EnsureTenantAdmin::class])
        ->name('tenant.analytics');
});
