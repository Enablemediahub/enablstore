<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TenantSetting;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->role === 'admin' && $request->user()->tenant_id === tenant()->getTenantKey()) {
            return redirect()->route('tenant.pos', ['tenant' => tenant()->getTenantKey()]);
        }

        if (! $request->session()->has('pos_cashier_id')) {
            $request->session()->put('workspace_destination', 'store');

            return Inertia::render('Tenant/Pos/Unlock', [
                'tenant' => (string) tenant()->getTenantKey(),
                'workspace' => 'store',
                'wallpaperUrl' => ($path = PlatformSetting::value('login_wallpaper'))
                    ? $request->getSchemeAndHttpHost().'/storage/'.ltrim($path, '/')
                    : $request->getSchemeAndHttpHost().'/images/products/cart.svg',
            ]);
        }

        return Inertia::render('Storefront/Index', [
            'products' => Product::query()
                ->with('inventoryStock')
                ->where('is_active', true)
                ->where('available_online', true)
                ->when((TenantSetting::query()->where('key', 'catalogue_mode')->value('value') ?? PlatformSetting::value('catalogue_mode_default', 'shared')) === 'separate_online', static fn ($query) => $query->where('available_in_pos', false))
                ->whereHas('inventoryStock', static fn ($query) => $query->where('quantity', '>', 0))
                ->orderBy('name')
                ->get(),
        ]);
    }
}
