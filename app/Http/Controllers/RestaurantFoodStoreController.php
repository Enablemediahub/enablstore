<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RestaurantMenuItem;
use App\Models\RestaurantOrder;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\Sale;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\RestaurantMenuPricing;
use App\Support\TenantPortalFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RestaurantFoodStoreController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if (tenant()->status === 'suspended') {
            return redirect()->route('tenant.subscription.suspended', ['tenant' => tenant()->getTenantKey()]);
        }

        $admin = $request->user()?->role === 'admin'
            && $request->user()->tenant_id === tenant()->getTenantKey();
        $cashier = User::query()
            ->whereKey($request->session()->get('pos_cashier_id'))
            ->where('tenant_id', tenant()->getTenantKey())
            ->where('role', 'cashier')
            ->first();

        if (! $admin && $cashier === null) {
            $request->session()->put('workspace_destination', 'foodstore');

            return Inertia::render('Tenant/Pos/Unlock', [
                'tenant' => (string) tenant()->getTenantKey(),
                'subscriberCode' => (string) (tenant()->subscriber_code ?: data_get(tenant()->data, 'subscriber_code', '')),
                'usernameHint' => (string) (User::query()
                    ->where('tenant_id', tenant()->getTenantKey())
                    ->where('role', 'cashier')
                    ->whereNotNull('pos_pin_hash')
                    ->orderBy('id')
                    ->value('username') ?? (tenant()->subscriber_code ?: data_get(tenant()->data, 'subscriber_code', '')).'-username'),
                'workspace' => 'foodstore',
                'wallpaperUrl' => ($path = PlatformSetting::value('login_wallpaper'))
                    ? $request->getSchemeAndHttpHost().'/storage/'.ltrim($path, '/')
                    : $request->getSchemeAndHttpHost().'/images/products/cart.svg',
            ]);
        }

        $subscription = tenant()->subscriptions()->with('plan')->latest()->first();
        if ($subscription === null
            || ! in_array($subscription->status, ['trialing', 'active'], true)
            || ! in_array('restaurant_foodstore', TenantPortalFeatures::forSubscription($subscription), true)) {
            return Inertia::render('Tenant/FeatureDenied', [
                'tenantName' => (string) (tenant()->name ?? 'your workspace'),
                'featureName' => 'FoodStore',
                'dashboardUrl' => route('dashboard'),
            ]);
        }

        $completedSaleId = $request->session()->pull('foodstore_completed_sale_id');
        $completedSale = $completedSaleId
            ? Sale::query()->with(['items.product', 'items.restaurantMenuItem', 'payments'])->find((int) $completedSaleId)
            : null;
        $todaySales = Sale::query()
            ->where('status', 'completed')
            ->where('source', 'foodstore')
            ->whereDate('completed_at', today());

        return Inertia::render('Tenant/Restaurant/Index', [
            'tenant' => (string) tenant()->getTenantKey(),
            'restaurantName' => (string) (tenant()->name ?? 'Restaurant'),
            'heroImageUrl' => PlatformSetting::foodStoreHeroImageUrl($request),
            'todaySalesMinor' => (clone $todaySales)->sum('total_minor'),
            'todaySalesCount' => (clone $todaySales)->count(),
            'operatorName' => $admin ? (string) $request->user()->name : $cashier->name,
            'cashierName' => $admin ? null : $cashier->name,
            'status' => session('status'),
            'menuItems' => RestaurantMenuItem::query()->orderBy('category')->orderBy('name')->get()
                    ->map(fn (RestaurantMenuItem $item): array => $this->menuItemPayload($item)),
            'completedReceipt' => $completedSale === null ? null : [
                'items' => $completedSale->items->map(static fn ($item): array => [
                    'name' => $item->item_name ?? $item->product->name ?? $item->restaurantMenuItem->name ?? 'Food item',
                    'quantity' => $item->quantity,
                    'priceMinor' => $item->unit_price_minor,
                    'selectedOptions' => $item->selected_options ?? [],
                ])->values(),
                'totalMinor' => $completedSale->total_minor,
                'paymentMethod' => $completedSale->payment_method,
                'tenders' => $completedSale->payments->map(static fn ($payment): array => [
                    'method' => $payment->method,
                    'amountMinor' => $payment->amount_minor,
                ])->values(),
                'transactionUuid' => $completedSale->transaction_uuid,
                'cashierName' => $completedSale->cashier_name ?? '',
                'customerName' => $completedSale->customer_name ?? '',
                'customerPhone' => $completedSale->customer_phone ?? '',
                'cashReceivedMinor' => $completedSale->payments->where('method', 'cash')->isEmpty()
                    ? null
                    : $completedSale->payments->sum('cash_received_minor'),
                'changeMinor' => max(0, $completedSale->payments->sum('cash_received_minor') - $completedSale->payments->where('method', 'cash')->sum('amount_minor')),
            ],
        ]);
    }

    public function online(): Response
    {
        return Inertia::render('Tenant/Restaurant/Online', [
            'tenant' => (string) tenant()->getTenantKey(),
            'restaurantName' => (string) (tenant()->name ?? 'Restaurant'),
            'whatsappPhone' => tenant()->whatsapp_phone,
            'heroImageUrl' => PlatformSetting::foodStoreHeroImageUrl(request()),
            'menuItems' => RestaurantMenuItem::query()->where('is_available', true)->orderBy('category')->orderBy('name')->get()
                ->map(fn (RestaurantMenuItem $item): array => $this->menuItemPayload($item)),
            'status' => session('status'),
        ]);
    }

    public function menu(Request $request): Response
    {
        return Inertia::render('Tenant/Restaurant/Menu', [
            'tenant' => (string) tenant()->getTenantKey(),
            'restaurantName' => (string) (tenant()->name ?? 'Restaurant'),
            'menuItems' => RestaurantMenuItem::query()->orderBy('category')->orderBy('name')->get()
                    ->map(fn (RestaurantMenuItem $item): array => $this->menuItemPayload($item)),
            'categories' => Category::query()->orderBy('name')->pluck('name')->values(),
            'status' => session('status'),
        ]);
    }

    public function orders(): Response
    {
        return Inertia::render('Tenant/Restaurant/Orders', [
            'tenant' => (string) tenant()->getTenantKey(),
            'restaurantName' => (string) (tenant()->name ?? 'Restaurant'),
            'orders' => RestaurantOrder::query()
                ->with('items')
                ->latest()
                ->limit(100)
                ->get()
                ->map(static fn (RestaurantOrder $order): array => [
                    'id' => $order->id,
                    'customerName' => $order->customer_name,
                    'customerPhone' => $order->customer_phone,
                    'notes' => $order->notes,
                    'status' => $order->status,
                    'totalMinor' => $order->total_minor,
                    'createdByName' => $order->created_by_name,
                    'createdAt' => $order->created_at?->toIso8601String(),
                    'items' => $order->items->map(static fn ($item): array => [
                        'name' => $item->item_name,
                        'quantity' => $item->quantity,
                        'unitPriceMinor' => $item->unit_price_minor,
                        'lineTotalMinor' => $item->line_total_minor,
                        'selectedOptions' => $item->selected_options ?? [],
                    ])->values(),
                ])->values(),
        ]);
    }

    public function storeMenuItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'price_ghs' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'unit_label' => ['required', 'string', 'max:32'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ...$this->optionGroupRules(),
        ]);

        RestaurantMenuItem::query()->create([
            'name' => trim($data['name']),
            'category' => trim($data['category']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'price_minor' => (int) round((float) $data['price_ghs'] * 100),
            'unit_label' => trim($data['unit_label']),
            'image_path' => $request->file('image')?->store('foodstore/'.tenant()->getTenantKey().'/menu', 'public'),
            'is_available' => true,
            'option_groups' => $this->normalizeOptionGroups($data['option_groups'] ?? []),
        ]);

        return back()->with('status', 'Menu item added.');
    }

    public function updateMenuItem(Request $request, RestaurantMenuItem $menuItem): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'category' => ['sometimes', 'required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'price_ghs' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:1000000'],
            'unit_label' => ['sometimes', 'required', 'string', 'max:32'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_available' => ['sometimes', 'boolean'],
            ...$this->optionGroupRules('sometimes'),
        ]);

        $attributes = [];
        foreach (['name', 'category', 'unit_label'] as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field] = trim($data[$field]);
            }
        }
        if (array_key_exists('description', $data)) {
            $attributes['description'] = filled($data['description']) ? trim($data['description']) : null;
        }
        if (array_key_exists('price_ghs', $data)) {
            $attributes['price_minor'] = (int) round((float) $data['price_ghs'] * 100);
        }
        if (array_key_exists('is_available', $data)) {
            $attributes['is_available'] = (bool) $data['is_available'];
        }
        if (array_key_exists('option_groups', $data)) {
            $attributes['option_groups'] = $this->normalizeOptionGroups($data['option_groups'] ?? []);
        }

        $previousImage = $menuItem->image_path;
        if ($request->hasFile('image')) {
            $attributes['image_path'] = $request->file('image')?->store('foodstore/'.tenant()->getTenantKey().'/menu', 'public');
        }

        $menuItem->update($attributes);
        if (isset($attributes['image_path']) && filled($previousImage)) {
            Storage::disk('public')->delete($previousImage);
        }

        $message = array_key_exists('is_available', $data)
            ? ($menuItem->is_available ? 'Menu item is available.' : 'Menu item is paused.')
            : 'Food item updated.';

        return back()->with('status', $message);
    }

    public function destroyMenuItem(RestaurantMenuItem $menuItem): RedirectResponse
    {
        if (filled($menuItem->image_path)) {
            Storage::disk('public')->delete($menuItem->image_path);
        }
        $menuItem->delete();

        return back()->with('status', 'Food item removed. Existing sales remain recorded.');
    }

    public function storeSale(Request $request, CheckoutService $checkoutService): RedirectResponse
    {
        $data = $request->validate([
            'transaction_uuid' => ['required', 'uuid'],
            'payment_method' => ['required', 'in:cash,mobile_money,card,split'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:restaurant_menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.selected_option_ids' => ['sometimes', 'array', 'max:120'],
            'items.*.selected_option_ids.*' => ['string', 'max:64', 'distinct'],
            'items.*.selected_options' => ['sometimes', 'array', 'max:120'],
            'items.*.selected_options.*.id' => ['required', 'string', 'max:64', 'distinct'],
            'items.*.selected_options.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'tenders' => ['required', 'array', 'min:1', 'max:3'],
            'tenders.*.method' => ['required', 'in:cash,mobile_money,card'],
            'tenders.*.amount_minor' => ['required', 'integer', 'min:1'],
            'tenders.*.cash_received_minor' => ['nullable', 'integer', 'min:0'],
            'tenders.*.externally_confirmed' => ['nullable', 'boolean'],
        ]);

        try {
            $sale = $checkoutService->checkout([...$data, 'source' => 'foodstore']);
        } catch (\DomainException $exception) {
            throw ValidationException::withMessages(['checkout' => $exception->getMessage()]);
        }

        $request->session()->flash('foodstore_completed_sale_id', $sale->id);

        return back()->with('status', 'Sale completed successfully.');
    }

    public function storeOrder(Request $request, RestaurantMenuPricing $pricing): RedirectResponse
    {
        $data = $request->validate([
            'table_label' => ['nullable', 'string', 'max:40'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:restaurant_menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.selected_option_ids' => ['sometimes', 'array', 'max:120'],
            'items.*.selected_option_ids.*' => ['string', 'max:64', 'distinct'],
            'items.*.selected_options' => ['sometimes', 'array', 'max:120'],
            'items.*.selected_options.*.id' => ['required', 'string', 'max:64', 'distinct'],
            'items.*.selected_options.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        return $this->createRestaurantOrder($data, $request, $pricing);
    }

    public function storeOnlineOrder(Request $request, RestaurantMenuPricing $pricing): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:40'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:restaurant_menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.selected_option_ids' => ['sometimes', 'array', 'max:120'],
            'items.*.selected_option_ids.*' => ['string', 'max:64', 'distinct'],
            'items.*.selected_options' => ['sometimes', 'array', 'max:120'],
            'items.*.selected_options.*.id' => ['required', 'string', 'max:64', 'distinct'],
            'items.*.selected_options.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'channel' => ['sometimes', 'in:website,whatsapp'],
        ]);

        if (($data['channel'] ?? 'website') === 'whatsapp') {
            $subscription = tenant()->subscriptions()->with('plan')->latest()->first();
            abort_unless(in_array('whatsapp_orders', TenantPortalFeatures::forSubscription($subscription), true), 402, 'WhatsApp ordering is not enabled for this workspace.');
        }

        return $this->createRestaurantOrder($data, $request, $pricing, true);
    }

    /** @param array<string, mixed> $data */
    private function createRestaurantOrder(array $data, Request $request, RestaurantMenuPricing $pricing, bool $online = false): RedirectResponse
    {

        try {
            DB::transaction(function () use ($data, $request, $pricing, $online): void {
                $lineItems = [];
                $totalMinor = 0;

                foreach ($data['items'] as $item) {
                    $menuItem = RestaurantMenuItem::query()
                        ->where('is_available', true)
                        ->lockForUpdate()
                        ->find($item['menu_item_id']);

                    if ($menuItem === null) {
                        throw new \DomainException('A menu item is no longer available. Refresh and review the order.');
                    }

                    $price = $pricing->forSelection(
                        $menuItem,
                        $item['selected_options'] ?? $item['selected_option_ids'] ?? [],
                    );
                    $lineTotal = $price['unit_price_minor'] * $item['quantity'];
                    $totalMinor += $lineTotal;
                    $lineItems[] = [
                        'restaurant_menu_item_id' => $menuItem->id,
                        'item_name' => $menuItem->name,
                        'quantity' => $item['quantity'],
                        'unit_price_minor' => $price['unit_price_minor'],
                        'line_total_minor' => $lineTotal,
                        'selected_options' => $price['selected_options'],
                    ];
                }

                $order = RestaurantOrder::query()->create([
                    'table_label' => filled($data['table_label'] ?? null) ? trim($data['table_label']) : null,
                    'customer_name' => filled($data['customer_name'] ?? null) ? trim($data['customer_name']) : null,
                    'customer_phone' => filled($data['customer_phone'] ?? null) ? trim($data['customer_phone']) : null,
                    'notes' => filled($data['notes'] ?? null) ? trim($data['notes']) : null,
                    'status' => 'queued',
                    'total_minor' => $totalMinor,
                    'created_by_name' => $online
                        ? (($data['channel'] ?? null) === 'whatsapp' ? 'Online customer via WhatsApp' : 'Online customer')
                        : ($request->user()?->name ?? User::query()
                        ->whereKey($request->session()->get('pos_cashier_id'))
                        ->where('tenant_id', tenant()->getTenantKey())
                        ->where('role', 'cashier')
                        ->value('name')),
                ]);
                $order->items()->createMany($lineItems);
            });
        } catch (\DomainException $exception) {
            throw ValidationException::withMessages(['items' => $exception->getMessage()]);
        }

        return back()->with('status', $online ? 'Your order has been sent to the restaurant.' : 'Order sent to the kitchen queue.');
    }

    public function updateOrderStatus(Request $request, RestaurantOrder $order): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:preparing,ready,served,cancelled']]);
        $transitions = [
            'queued' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['served', 'cancelled'],
            'served' => [],
            'cancelled' => [],
        ];

        if (! in_array($data['status'], $transitions[$order->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'That order status transition is not allowed.']);
        }

        $order->update(['status' => $data['status']]);

        return back()->with('status', 'Order moved to '.str_replace('_', ' ', $data['status']).'.');
    }

    /** @return array<string, mixed> */
    private function menuItemPayload(RestaurantMenuItem $item): array
    {
        return [
            ...$item->toArray(),
            'option_groups' => $item->option_groups ?? [],
            'image_url' => filled($item->image_path)
                ? route('tenant.media', [
                    'tenant' => tenant()->getTenantKey(),
                    'path' => $item->image_path,
                ])
                : null,
        ];
    }

    /** @return array<string, array<int, string>> */
    private function optionGroupRules(string $presence = 'sometimes'): array
    {
        return [
            'option_groups' => [$presence, 'array', 'max:8'],
            'option_groups.*.id' => ['required', 'string', 'max:64', 'distinct'],
            'option_groups.*.name' => ['required', 'string', 'max:60'],
            'option_groups.*.required' => ['required', 'boolean'],
            'option_groups.*.multiple' => ['required', 'boolean'],
            'option_groups.*.options' => ['required', 'array', 'min:1', 'max:15'],
            'option_groups.*.options.*.id' => ['required', 'string', 'max:64', 'distinct'],
            'option_groups.*.options.*.name' => ['required', 'string', 'max:60'],
            'option_groups.*.options.*.price_ghs' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }

    /** @param array<int, array<string, mixed>> $groups
     *  @return array<int, array{id: string, name: string, required: bool, multiple: bool, options: array<int, array{id: string, name: string, price_minor: int}>}>
     */
    private function normalizeOptionGroups(array $groups): array
    {
        return array_map(static fn (array $group): array => [
            'id' => $group['id'],
            'name' => trim($group['name']),
            'required' => (bool) $group['required'],
            'multiple' => (bool) $group['multiple'],
            'options' => array_map(static fn (array $option): array => [
                'id' => $option['id'],
                'name' => trim($option['name']),
                'price_minor' => (int) round((float) $option['price_ghs'] * 100),
            ], $group['options']),
        ], $groups);
    }
}