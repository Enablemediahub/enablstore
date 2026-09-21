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
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantSupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $purchases = StockMovement::query()->with(['supplier:id,name', 'product:id,name,sku'])->where('type', 'purchase')
            ->when($search !== '', fn ($query) => $query->where(fn ($purchaseQuery) => $purchaseQuery->whereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%"))->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))->orWhere('note', 'like', "%{$search}%")));

        return Inertia::render('Tenant/Suppliers/Index', [
            'suppliers' => Supplier::query()->where('is_active', true)->when($search !== '', fn ($query) => $query->where(fn ($supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%")->orWhere('contact_name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))->latest()->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'purchase_unit', 'units_per_purchase']),
            'purchases' => (clone $purchases)->latest('purchased_at')->limit(100)->get(),
            'metrics' => ['supplier_count' => Supplier::query()->where('is_active', true)->count(), 'receipts_count' => (clone $purchases)->count(), 'units_received' => (int) (clone $purchases)->sum('quantity'), 'spend_minor' => (int) (clone $purchases)->selectRaw('COALESCE(SUM(quantity * unit_cost_minor), 0) as total')->value('total')],
            'filters' => ['search' => $search],
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
