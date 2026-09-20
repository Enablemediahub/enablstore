<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(Request $request): Response
    {
        if (! $request->session()->has('pos_cashier_id')) {
            return Inertia::render('Tenant/Pos/Unlock', [
                'tenant' => (string) tenant()->getTenantKey(),
            ]);
        }

        return Inertia::render('Tenant/Pos/Index', [
            'tenant' => (string) tenant()->getTenantKey(),
            'products' => Product::query()
                ->with('inventoryStock')
                ->where('is_active', true)
                ->where('available_in_pos', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function manifest(): JsonResponse
    {
        $tenant = (string) tenant()->getTenantKey();

        return response()->json([
            'name' => tenant()->name.' POS',
            'short_name' => 'POS',
            'description' => 'Enablstore point of sale',
            'start_url' => route('tenant.pos', ['tenant' => $tenant]),
            'scope' => url('/client/'.$tenant.'/'),
            'display' => 'standalone',
            'background_color' => '#f9fafb',
            'theme_color' => '#059669',
            'icons' => [
                [
                    'src' => asset('images/Enablstore-cropped.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ])->header('Cache-Control', 'no-store');
    }

    public function checkout(
        CheckoutRequest $request,
        CheckoutService $checkoutService,
    ): RedirectResponse {
        $checkoutService->checkout($request->validated());

        return back()->with('success', 'Sale completed successfully.');
    }
}
