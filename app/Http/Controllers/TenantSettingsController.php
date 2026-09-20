<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TenantSetting;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantSettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tenant/Settings/Index', [
            'catalogueMode' => TenantSetting::query()->where('key', 'catalogue_mode')->value('value') ?? PlatformSetting::value('catalogue_mode_default', 'shared'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'catalogue_mode' => ['required', 'in:shared,separate_online'],
        ]);

        TenantSetting::query()->updateOrCreate(
            ['key' => 'catalogue_mode'],
            ['value' => $validated['catalogue_mode']],
        );

        return back()->with('success', 'Catalogue settings updated.');
    }
}