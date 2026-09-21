<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PlatformSetting;
use App\Models\TenantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $admin = $request->user()?->role === 'admin'
            && $request->user()->tenant_id === tenant()->getTenantKey();

        if (! $admin && ! $request->session()->has('pos_cashier_id')) {
            $request->session()->put('workspace_destination', 'store');

            return Inertia::render('Tenant/Pos/Unlock', [
                'tenant' => (string) tenant()->getTenantKey(),
                'workspace' => 'store',
                'wallpaperUrl' => ($path = PlatformSetting::value('login_wallpaper'))
                    ? $request->getSchemeAndHttpHost().'/storage/'.ltrim($path, '/')
                    : $request->getSchemeAndHttpHost().'/images/products/cart.svg',
            ]);
        }

        $catalogueMode = TenantSetting::query()->where('key', 'catalogue_mode')->value('value')
            ?? PlatformSetting::value('catalogue_mode_default', 'shared');

        return Inertia::render('Storefront/Index', [
            'hero' => TenantSettingsController::heroSettingsForStorefront($request),
            'logoUrl' => PlatformSetting::storefrontLogoUrl($request),
            'tenantDisplay' => PlatformSetting::storefrontTenantDisplayForStorefront(),
            'storefront' => TenantSettingsController::storefrontConfigForStorefront($request),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()
                ->with(['category:id,name', 'inventoryStock'])
                ->withSum('saleItems as total_sold', 'quantity')
                ->where('is_active', true)
                ->where('available_online', true)
                ->when($catalogueMode === 'separate_online', static fn ($query) => $query->where('available_in_pos', false))
                ->whereHas('inventoryStock', static fn ($query) => $query->where('quantity', '>', 0))
                ->orderBy('name')
                ->get()
                ->map(static fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price_minor' => $product->price_minor,
                    'compare_at_price_minor' => $product->compare_at_price_minor,
                    'is_online_deal' => $product->is_online_deal,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category?->name,
                    'total_sold' => (int) ($product->total_sold ?? 0),
                    'created_at' => $product->created_at?->toIso8601String(),
                    'image_path' => $product->image_path,
                    'image_gallery' => $product->image_gallery,
                ]),
        ]);
    }
}
