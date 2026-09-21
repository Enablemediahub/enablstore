<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuperAdminBrandingController extends Controller
{
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
