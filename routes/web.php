<?php

use App\Filament\SuperAdmin\Pages\TenantManagement;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminAccountController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Http\Controllers\SuperAdminBrandingController;
use App\Http\Controllers\SuperAdminCatalogueController;
use App\Http\Controllers\SuperAdminSubscriptionController;
use App\Http\Controllers\SuperAdminTenantController;
use App\Http\Controllers\SuperAdminUserController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\WorkspaceDashboardController;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', WorkspaceDashboardController::class)
    ->name('dashboard');

Route::get('/design-system', function () {
    return Inertia::render('DesignSystem/Index');
})->name('design-system');

Route::get('/tenant/register', [TenantRegistrationController::class, 'create'])
    ->name('tenant.register');
Route::post('/tenant/register', [TenantRegistrationController::class, 'store'])
    ->name('tenant.register.store');

Route::post('/webhooks/paystack', PaystackWebhookController::class)
    ->name('webhooks.paystack');

Route::get('/super-admin-preview/{path?}', function (Request $request, ?string $path = null) {
    $destination = '/super-admin'.(filled($path) ? '/'.ltrim($path, '/') : '');
    $query = $request->getQueryString();

    return redirect($destination.($query ? '?'.$query : ''));
})->where('path', '.*');
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'store'])
    ->name('super-admin.login.store');
Route::middleware('auth:super_admin')->prefix('super-admin')->name('super-admin.')->group(function (): void {
    Route::get('/dashboard', fn () => redirect()->route('filament.super-admin.pages.dashboard'))->name('dashboard');
    Route::get('/users', fn () => redirect()->route('filament.super-admin.pages.tenant-directory'))->name('users.index');
    Route::post('/users', [SuperAdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [SuperAdminUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/reset-access', [SuperAdminUserController::class, 'resetAccess'])->name('users.reset-access');
    Route::delete('/users/{user}', [SuperAdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/tenants/{tenant}/storefront-logo', [SuperAdminBrandingController::class, 'updateTenantStorefrontLogo'])->name('tenants.storefront-logo');
    Route::get('/accounts', fn () => redirect()->route('filament.super-admin.pages.admin-accounts'))->name('accounts.index');
    Route::post('/accounts', [SuperAdminAccountController::class, 'store'])->name('accounts.store');
    Route::patch('/accounts/{superAdmin}', [SuperAdminAccountController::class, 'update'])->name('accounts.update');
    Route::post('/branding/dashboard-wallpaper', [SuperAdminBrandingController::class, 'updateDashboardWallpaper'])->name('branding.dashboard-wallpaper');
    Route::post('/branding/login-wallpaper', [SuperAdminBrandingController::class, 'updateLoginWallpaper'])->name('branding.login-wallpaper');
    Route::post('/branding/pos-hero-image', [SuperAdminBrandingController::class, 'updatePosHeroImage'])->name('branding.pos-hero-image');
    Route::post('/branding/foodstore-hero-image', [SuperAdminBrandingController::class, 'updateFoodStoreHeroImage'])->name('branding.foodstore-hero-image');
    Route::post('/branding/storefront-logo', [SuperAdminBrandingController::class, 'updateStorefrontLogo'])->name('branding.storefront-logo');
    Route::delete('/branding/storefront-logo', [SuperAdminBrandingController::class, 'destroyStorefrontLogo'])->name('branding.storefront-logo.destroy');
    Route::patch('/branding/storefront-tenant-display', [SuperAdminBrandingController::class, 'updateStorefrontTenantDisplay'])->name('branding.storefront-tenant-display');
    Route::patch('/catalogue-mode', [SuperAdminCatalogueController::class, 'update'])->name('catalogue-mode.update');
    Route::get('/subscription-settings', fn () => redirect()->route('filament.super-admin.pages.subscription-plans'))->name('subscriptions.settings');
    Route::get('/financial', function (Request $request) {
        return redirect()->route('filament.super-admin.pages.financial-overview', array_filter([
            'tableSearch' => trim((string) $request->query('search', '')) ?: null,
            'provider' => $request->query('provider') === 'all' ? null : $request->query('provider'),
            'status' => $request->query('status') === 'all' ? null : $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'renewal_window' => $request->query('renewal_window') === 'all' ? null : $request->query('renewal_window'),
        ]));
    })->name('financial.index');
    Route::post('/subscription-settings/plans', [SuperAdminSubscriptionController::class, 'store'])->name('subscriptions.plans.store');
    Route::patch('/subscription-settings/plans/{plan}', [SuperAdminSubscriptionController::class, 'update'])->name('subscriptions.plans.update');
    Route::get('/tenants', function (Request $request) {
        return redirect()->route('filament.super-admin.pages.tenant-directory', array_filter([
            'tableSearch' => trim((string) $request->query('search', '')) ?: null,
            'feature' => $request->query('feature'),
        ]));
    })->name('tenants.index');
    Route::get('/tenants/{tenant}', function (Tenant $tenant) {
        return redirect()->to(TenantManagement::getUrl(['tenant' => $tenant->id], panel: 'super-admin'));
    })->name('tenants.show');
    Route::patch('/tenants/{tenant}', [SuperAdminTenantController::class, 'update'])->name('tenants.update');
    Route::patch('/tenants/{tenant}/activate', [SuperAdminTenantController::class, 'activateManually'])->name('tenants.activate');
    Route::patch('/tenants/{tenant}/paystack', [SuperAdminTenantController::class, 'updatePaystackSettings'])->name('tenants.paystack.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
