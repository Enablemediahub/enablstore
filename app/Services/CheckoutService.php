<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * @param array{transaction_uuid: string, payment_method: string, customer_name?: string|null, customer_phone?: string|null, items: array<int, array{product_id: int, quantity: int}>, discount_type?: string|null, discount_value?: float|int|null, discount_reason?: string|null} $payload
     */
    public function checkout(array $payload): Sale
    {
        return DB::transaction(function () use ($payload): Sale {
            $existingSale = Sale::query()->where('transaction_uuid', $payload['transaction_uuid'])->first();

            if ($existingSale !== null) {
                return $existingSale;
            }

            $lineItems = [];
            $subtotalMinor = 0;

            foreach ($payload['items'] as $item) {
                $product = Product::query()->find($item['product_id']);

                if ($product === null) {
                    throw (new ModelNotFoundException)->setModel(Product::class, [$item['product_id']]);
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

            $sale = Sale::query()->create([
                'transaction_uuid' => $payload['transaction_uuid'] ?: (string) Str::uuid(),
                'customer_name' => filled($payload['customer_name'] ?? null) ? trim((string) $payload['customer_name']) : null,
                'customer_phone' => filled($payload['customer_phone'] ?? null) ? trim((string) $payload['customer_phone']) : null,
                'subtotal_minor' => $subtotalMinor,
                'discount_minor' => $discountMinor,
                'discount_type' => $payload['discount_type'] ?? null,
                'discount_reason' => $payload['discount_reason'] ?? null,
                'total_minor' => $subtotalMinor - $discountMinor,
                'currency' => 'GHS',
                'payment_method' => $payload['payment_method'],
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $sale->items()->createMany($lineItems);
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
}
