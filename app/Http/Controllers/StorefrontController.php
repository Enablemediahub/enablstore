<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TenantSetting;
use App\Models\PlatformSetting;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function index(): Response
    {
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
