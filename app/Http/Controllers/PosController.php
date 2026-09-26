<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PlatformSetting;
use App\Models\Sale;
use App\Models\User;
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

        $completedSaleId = $request->session()->pull('pos_completed_sale_id');
        $completedSale = $completedSaleId
            ? Sale::query()->with(['items.product', 'payments'])->find($completedSaleId)
            : null;

        return Inertia::render('Tenant/Pos/Index', [
            'tenant' => (string) tenant()->getTenantKey(),
            'cashierName' => $cashier->name,
            'heroImageUrl' => PlatformSetting::posHeroImageUrl($request),
            'completedReceipt' => $completedSale === null ? null : [
                'items' => $completedSale->items->map(static fn ($item): array => [
                    'name' => $item->product?->name ?? 'Product',
                    'quantity' => $item->quantity,
                    'priceMinor' => $item->unit_price_minor,
                ])->values(),
                'totalMinor' => $completedSale->total_minor,
                'paymentMethod' => $completedSale->payment_method,
                'tenders' => $completedSale->payments->map(static fn ($payment): array => [
                    'method' => $payment->method,
                    'amountMinor' => $payment->amount_minor,
                ])->values(),
                'transactionUuid' => $completedSale->transaction_uuid,
                'cashierName' => $completedSale->cashier_name ?? $cashier->name,
                'customerName' => $completedSale->customer_name ?? '',
                'customerPhone' => $completedSale->customer_phone ?? '',
                'cashReceivedMinor' => $completedSale->payments->where('method', 'cash')->isEmpty()
                    ? null
                    : $completedSale->payments->sum('cash_received_minor'),
                'changeMinor' => max(0, $completedSale->payments->sum('cash_received_minor') - $completedSale->payments->where('method', 'cash')->sum('amount_minor')),
            ],
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
            'scope' => url('/pos/'.$tenant.'/'),
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

}
