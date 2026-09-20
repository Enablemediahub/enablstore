<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\StockMovement;
use App\Http\Requests\ReceiveStockRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantSupplierController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Tenant/Suppliers/Index', [
            'suppliers' => Supplier::query()->where('is_active', true)->latest()->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'purchase_unit', 'units_per_purchase']),
            'purchases' => StockMovement::query()->with(['supplier:id,name', 'product:id,name,sku'])->where('type', 'purchase')->latest('purchased_at')->limit(100)->get(),
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::query()->create($request->validated());
        AuditLog::query()->create([
            'action' => 'supplier.created',
            'auditable_type' => Supplier::class,
            'auditable_id' => (string) $supplier->id,
            'metadata' => ['name' => $supplier->name],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Supplier added successfully.');
    }

    public function receive(ReceiveStockRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $product = Product::query()->findOrFail($request->integer('product_id'));
            $stock = $product->inventoryStock()->lockForUpdate()->firstOrFail();
            $purchaseQuantity = $request->integer('quantity');
            $unitsReceived = $purchaseQuantity * max(1, (int) $product->units_per_purchase);
            $unitCostMinor = intdiv($request->integer('unit_cost_minor'), max(1, (int) $product->units_per_purchase));
            $stock->increment('quantity', $unitsReceived);
            $movement = StockMovement::query()->create([
                ...$request->validated(),
                'quantity' => $unitsReceived,
                'unit_cost_minor' => $unitCostMinor,
                'type' => 'purchase',
            ]);
            AuditLog::query()->create([
                'action' => 'stock.received',
                'auditable_type' => StockMovement::class,
                'auditable_id' => (string) $movement->id,
                'metadata' => ['purchase_quantity' => $purchaseQuantity, 'purchase_unit' => $product->purchase_unit, 'units_received' => $unitsReceived, 'unit_cost_minor' => $unitCostMinor],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', 'Stock receipt recorded successfully.');
    }
}