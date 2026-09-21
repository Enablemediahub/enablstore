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
}
