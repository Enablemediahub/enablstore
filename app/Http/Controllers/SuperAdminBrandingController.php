<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuperAdminBrandingController extends Controller
{
    public function updateDashboardWallpaper(Request $request): RedirectResponse
    {
        $request->validate([
            'dashboard_wallpaper' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $previousPath = PlatformSetting::value('dashboard_wallpaper');
        $path = $request->file('dashboard_wallpaper')->store('platform', 'public');

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'dashboard_wallpaper'],
            ['value' => $path],
        );

        if (is_string($previousPath) && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'Dashboard wallpaper updated.');
    }

    public function updateLoginWallpaper(Request $request): RedirectResponse
    {
        $request->validate([
            'login_wallpaper' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $previousPath = PlatformSetting::value('login_wallpaper');
        $path = $request->file('login_wallpaper')->store('platform', 'public');

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'login_wallpaper'],
            ['value' => $path],
        );

        if (is_string($previousPath) && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'Login wallpaper updated.');
    }

    public function updatePosHeroImage(Request $request): RedirectResponse
    {
        $request->validate([
            'pos_hero_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $previousPath = PlatformSetting::value('pos_hero_image');
        $path = $request->file('pos_hero_image')->store('platform', 'public');

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'pos_hero_image'],
            ['value' => $path],
        );

        if (is_string($previousPath) && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'POS hero image updated.');
    }

    public function updateFoodStoreHeroImage(Request $request): RedirectResponse
    {
        $request->validate([
            'foodstore_hero_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $previousPath = PlatformSetting::value('foodstore_hero_image');
        $path = $request->file('foodstore_hero_image')->store('platform', 'public');

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'foodstore_hero_image'],
            ['value' => $path],
        );

        if (is_string($previousPath) && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'FoodStore hero image updated.');
    }

    public function updateStorefrontLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'storefront_logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
        ]);

        $previousPath = PlatformSetting::value('storefront_logo');
        $path = $request->file('storefront_logo')->store('platform', 'public');

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'storefront_logo'],
            ['value' => $path],
        );

        if (is_string($previousPath) && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'Storefront logo updated.');
    }

    public function updateTenantStorefrontLogo(Request $request, Tenant $tenant): RedirectResponse
    {
        $request->validate([
            'storefront_logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
        ]);

        $data = is_array($tenant->data) ? $tenant->data : [];
        $previousPath = $data['storefront_logo'] ?? null;

        if (filled($previousPath)) {
            return back()->withErrors([
                'storefront_logo' => 'This subscriber logo is permanent and cannot be changed.',
            ]);
        }

        $path = $request->file('storefront_logo')->store('tenants/'.$tenant->getTenantKey().'/branding', 'public');
        $data['storefront_logo'] = $path;
        $tenant->update(['data' => $data]);

        if (is_string($previousPath) && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'Tenant storefront logo updated.');
    }

    public function destroyStorefrontLogo(): RedirectResponse
    {
        $previousPath = PlatformSetting::value('storefront_logo');

        PlatformSetting::query()->where('key', 'storefront_logo')->delete();

        if (is_string($previousPath) && $previousPath !== '') {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'Storefront logo reset to default.');
    }

    public function updateStorefrontTenantDisplay(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'placement' => ['required', 'in:header,hero,both'],
            'label_prefix' => ['nullable', 'string', 'max:40'],
        ]);

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'storefront_tenant_display'],
            ['value' => json_encode([
                'enabled' => $validated['enabled'],
                'placement' => $validated['placement'],
                'label_prefix' => filled($validated['label_prefix'] ?? null)
                    ? trim((string) $validated['label_prefix'])
                    : 'Shopping at',
            ], JSON_THROW_ON_ERROR)],
        );

        return back()->with('status', 'Storefront tenant display updated.');
    }
}
