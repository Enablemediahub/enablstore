<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Product;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $admin = $request->user()?->role === 'admin'
            && $request->user()->tenant_id === tenant()->getTenantKey();

        if (! $admin && ! $request->session()->has('pos_cashier_id')) {
            $request->session()->put('workspace_destination', 'pos');

            return Inertia::render('Tenant/Pos/Unlock', [
                'tenant' => (string) tenant()->getTenantKey(),
                'workspace' => 'pos',
                'wallpaperUrl' => ($path = PlatformSetting::value('login_wallpaper'))
                    ? $request->getSchemeAndHttpHost().'/storage/'.ltrim($path, '/')
                    : $request->getSchemeAndHttpHost().'/images/products/cart.svg',
            ]);
        }

        $cashier = $admin
            ? $request->user()
            : User::query()->find($request->session()->get('pos_cashier_id'));
        if ($cashier === null) {
            $request->session()->forget('pos_cashier_id');

            return redirect()->route('tenant.pos', ['tenant' => tenant()->getTenantKey()]);
        }

        return Inertia::render('Tenant/Pos/Index', [
            'tenant' => (string) tenant()->getTenantKey(),
            'cashierName' => $cashier->name,
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
