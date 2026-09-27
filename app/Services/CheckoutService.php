<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\RestaurantMenuItem;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /** @param array{items: array<int, array{product_id: int, quantity: int}>, source?: string, discount_type?: string|null, discount_value?: float|int|null} $payload
     *  @return array{subtotal_minor: int, discount_minor: int, total_minor: int}
     */
    public function quote(array $payload): array
    {
        $subtotalMinor = 0;

        foreach ($payload['items'] as $item) {
            $product = Product::query()->find($item['product_id']);

            if ($product === null) {
                throw (new ModelNotFoundException)->setModel(Product::class, [$item['product_id']]);
            }

            if (($payload['source'] ?? null) === 'storefront' && (! $product->is_active || ! $product->available_online)) {
                throw new \DomainException("{$product->name} is not available for online purchase.");
            }

            $stock = InventoryStock::query()->where('product_id', $product->id)->first();
            if ($stock === null || $stock->quantity < $item['quantity']) {
                throw new \DomainException("Insufficient stock for {$product->name}.");
            }

            $subtotalMinor += $product->price_minor * $item['quantity'];
        }

        $discountValue = (float) ($payload['discount_value'] ?? 0);
        $discountMinor = ($payload['discount_type'] ?? null) === 'percentage'
            ? (int) round($subtotalMinor * min($discountValue, 100) / 100)
            : (int) round($discountValue * 100);
        $discountMinor = min($discountMinor, $subtotalMinor);

        return [
            'subtotal_minor' => $subtotalMinor,
            'discount_minor' => $discountMinor,
            'total_minor' => $subtotalMinor - $discountMinor,
        ];
    }

    /**
    * @param array{transaction_uuid: string, payment_method: string, customer_name?: string|null, customer_phone?: string|null, customer_email?: string|null, delivery_location?: string|null, items: array<int, array{product_id?: int, menu_item_id?: int, quantity: int, ...}>, tenders?: array<int, array{method: string, amount_minor: int, cash_received_minor?: int|null, externally_confirmed?: bool, provider_reference?: string, ...}>, source?: string, discount_type?: string|null, discount_value?: float|int|null, discount_reason?: string|null, ...} $payload
     * @return Sale
     */
    public function checkout(array $payload): Sale
    {
        $cashierName = $this->operatorName($payload);
        $foodstore = ($payload['source'] ?? null) === 'foodstore';

        return DB::transaction(function () use ($payload, $cashierName, $foodstore): Sale {
            $existingSale = Sale::query()->where('transaction_uuid', $payload['transaction_uuid'])->first();

            if ($existingSale !== null) {
                return $existingSale;
            }

            $lineItems = [];
            $subtotalMinor = 0;

            foreach ($payload['items'] as $item) {
                if ($foodstore) {
                    $menuItem = RestaurantMenuItem::query()
                        ->where('is_available', true)
                        ->lockForUpdate()
                        ->find($item['menu_item_id']);

                    if ($menuItem === null) {
                        throw new \DomainException('A food item is no longer available. Refresh and review the cart.');
                    }

                    $lineTotalMinor = $menuItem->price_minor * $item['quantity'];
                    $subtotalMinor += $lineTotalMinor;
                    $lineItems[] = [
                        'product_id' => null,
                        'restaurant_menu_item_id' => $menuItem->id,
                        'item_name' => $menuItem->name,
                        'quantity' => $item['quantity'],
                        'unit_price_minor' => $menuItem->price_minor,
                        'line_total_minor' => $lineTotalMinor,
                    ];

                    continue;
                }

                $product = Product::query()->find($item['product_id']);

                if ($product === null) {
                    throw (new ModelNotFoundException)->setModel(Product::class, [$item['product_id']]);
                }

                if (($payload['source'] ?? null) === 'storefront' && (! $product->is_active || ! $product->available_online)) {
                    throw new \DomainException("{$product->name} is no longer available for online purchase.");
                }

                $stock = InventoryStock::query()->where('product_id', $product->id)->lockForUpdate()->firstOrFail();

                if ($stock->quantity < $item['quantity']) {
                    throw new \DomainException("Insufficient stock for {$product->name}.");
                }

                $lineTotalMinor = $product->price_minor * $item['quantity'];
                $subtotalMinor += $lineTotalMinor;
                $stock->decrement('quantity', $item['quantity']);
                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'note' => 'POS sale '.$payload['transaction_uuid'],
                ]);
                $lineItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price_minor' => $product->price_minor,
                    'line_total_minor' => $lineTotalMinor,
                ];
            }

            $discountValue = (float) ($payload['discount_value'] ?? 0);
            $discountMinor = ($payload['discount_type'] ?? null) === 'percentage'
                ? (int) round($subtotalMinor * min($discountValue, 100) / 100)
                : (int) round($discountValue * 100);
            $discountMinor = min($discountMinor, $subtotalMinor);
            $totalMinor = $subtotalMinor - $discountMinor;
            $tenders = $payload['tenders'] ?? [[
                'method' => $payload['payment_method'],
                'amount_minor' => $totalMinor,
                'cash_received_minor' => $payload['payment_method'] === 'cash' ? $totalMinor : null,
            ]];

            if (array_sum(array_column($tenders, 'amount_minor')) !== $totalMinor) {
                throw new \DomainException('Payment amounts must add up to the sale total.');
            }

            foreach ($tenders as $tender) {
                if ($tender['method'] === 'cash'
                    && (int) ($tender['cash_received_minor'] ?? 0) < (int) $tender['amount_minor']) {
                    throw new \DomainException('Cash received must cover the cash amount applied.');
                }
            }

            foreach ($tenders as $tender) {
                if ($tender['method'] !== 'cash' && ($payload['source'] ?? 'pos') === 'storefront'
                    && ! filled($tender['provider_reference'] ?? null)) {
                    throw new \DomainException('Storefront digital payments must be verified before completing the sale.');
                }

                if ($tender['method'] !== 'cash' && in_array(($payload['source'] ?? 'pos'), ['pos', 'foodstore'], true)
                    && ! ($tender['externally_confirmed'] ?? false)) {
                    throw new \DomainException('Confirm the payment was received on the external terminal.');
                }
            }

            $sale = Sale::query()->create([
                'transaction_uuid' => $payload['transaction_uuid'] ?: (string) Str::uuid(),
                'cashier_name' => $cashierName,
                'customer_name' => filled($payload['customer_name'] ?? null) ? trim((string) $payload['customer_name']) : null,
                'customer_phone' => filled($payload['customer_phone'] ?? null) ? trim((string) $payload['customer_phone']) : null,
                'customer_email' => filled($payload['customer_email'] ?? null) ? trim((string) $payload['customer_email']) : null,
                'subtotal_minor' => $subtotalMinor,
                'discount_minor' => $discountMinor,
                'discount_type' => $payload['discount_type'] ?? null,
                'discount_reason' => $payload['discount_reason'] ?? null,
                'total_minor' => $totalMinor,
                'currency' => 'GHS',
                'payment_method' => count($tenders) > 1 ? 'split' : $tenders[0]['method'],
                'source' => $payload['source'] ?? 'pos',
                'delivery_location' => $payload['delivery_location'] ?? null,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $sale->items()->createMany($lineItems);
            $sale->payments()->createMany(array_map(static fn (array $tender): array => [
                'method' => $tender['method'],
                'amount_minor' => $tender['amount_minor'],
                'cash_received_minor' => $tender['cash_received_minor'] ?? null,
                'externally_confirmed' => (bool) ($tender['externally_confirmed'] ?? false),
                'provider_reference' => $tender['provider_reference'] ?? null,
            ], $tenders));
            AuditLog::query()->create([
                'action' => 'sale.completed',
                'auditable_type' => Sale::class,
                'auditable_id' => (string) $sale->id,
                'metadata' => ['total_minor' => $sale->total_minor, 'discount_minor' => $discountMinor],
                'ip_address' => request()->ip(),
            ]);

            return $sale->load('items');
        });
    }

    /** @param array<string, mixed> $payload */
    private function operatorName(array $payload): ?string
    {
        if (($payload['source'] ?? null) === 'storefront') {
            return null;
        }

        $user = request()->user();

        if ($user?->role === 'admin' && $user->tenant_id === tenant()->getTenantKey()) {
            return $user->name;
        }

        $cashierId = request()->session()->get('pos_cashier_id');

        if (! $cashierId) {
            return null;
        }

        $name = User::query()
            ->where('id', $cashierId)
            ->where('tenant_id', tenant()->getTenantKey())
            ->where('role', 'cashier')
            ->value('name');

        return is_string($name) ? $name : null;
    }
}
