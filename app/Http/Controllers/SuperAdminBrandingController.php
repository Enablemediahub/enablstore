<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuperAdminBrandingController extends Controller
{
    public function update(Request $request): RedirectResponse
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
}
