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
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']),
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
            $stock = Product::query()->findOrFail($request->integer('product_id'))->inventoryStock()->lockForUpdate()->firstOrFail();
            $stock->increment('quantity', $request->integer('quantity'));
            $movement = StockMovement::query()->create([
                ...$request->validated(),
                'type' => 'purchase',
            ]);
            AuditLog::query()->create([
                'action' => 'stock.received',
                'auditable_type' => StockMovement::class,
                'auditable_id' => (string) $movement->id,
                'metadata' => ['quantity' => $movement->quantity, 'unit_cost_minor' => $movement->unit_cost_minor],
                'ip_address' => $request->ip(),
            ]);
        });

        return back()->with('success', 'Stock receipt recorded successfully.');
    }
}