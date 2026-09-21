<?php

use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\TenantRegistrationController;
use App\Http\Controllers\WorkspaceDashboardController;
use App\Http\Controllers\SuperAdminBrandingController;
use App\Http\Controllers\SuperAdminUserController;
use App\Http\Controllers\SuperAdminAccountController;
use App\Http\Controllers\SuperAdminCatalogueController;
use App\Http\Controllers\SuperAdminTenantController;
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

Route::get('/super-admin/login', [SuperAdminAuthController::class, 'create'])
    ->name('super-admin.login');
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'store'])
    ->name('super-admin.login.store');
Route::middleware('auth:super_admin')->prefix('super-admin')->name('super-admin.')->group(function (): void {
    Route::get('/dashboard', SuperAdminDashboardController::class)->name('dashboard');
    Route::get('/users', fn () => redirect()->route('super-admin.tenants.index'))->name('users.index');
    Route::post('/users', [SuperAdminUserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [SuperAdminUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/reset-access', [SuperAdminUserController::class, 'resetAccess'])->name('users.reset-access');
    Route::delete('/users/{user}', [SuperAdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/accounts', [SuperAdminAccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [SuperAdminAccountController::class, 'store'])->name('accounts.store');
    Route::patch('/accounts/{superAdmin}', [SuperAdminAccountController::class, 'update'])->name('accounts.update');
    Route::post('/branding/dashboard-wallpaper', [SuperAdminBrandingController::class, 'updateDashboardWallpaper'])->name('branding.dashboard-wallpaper');
    Route::post('/branding/login-wallpaper', [SuperAdminBrandingController::class, 'updateLoginWallpaper'])->name('branding.login-wallpaper');
    Route::post('/branding/storefront-logo', [SuperAdminBrandingController::class, 'updateStorefrontLogo'])->name('branding.storefront-logo');
    Route::delete('/branding/storefront-logo', [SuperAdminBrandingController::class, 'destroyStorefrontLogo'])->name('branding.storefront-logo.destroy');
    Route::patch('/branding/storefront-tenant-display', [SuperAdminBrandingController::class, 'updateStorefrontTenantDisplay'])->name('branding.storefront-tenant-display');
    Route::patch('/catalogue-mode', [SuperAdminCatalogueController::class, 'update'])->name('catalogue-mode.update');
    Route::get('/tenants', [SuperAdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/{tenant}', [SuperAdminTenantController::class, 'show'])->name('tenants.show');
    Route::patch('/tenants/{tenant}', [SuperAdminTenantController::class, 'update'])->name('tenants.update');
    Route::post('/logout', [SuperAdminAuthController::class, 'destroy'])->name('logout');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
