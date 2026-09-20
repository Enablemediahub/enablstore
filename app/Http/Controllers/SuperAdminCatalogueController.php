<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuperAdminCatalogueController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'catalogue_mode' => ['required', 'in:shared,separate_online'],
        ]);

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'catalogue_mode_default'],
            ['value' => $validated['catalogue_mode']],
        );

        return back()->with('status', 'Default catalogue mode updated.');
    }
}
