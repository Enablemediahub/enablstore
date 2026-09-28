<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\TenantSetting;
use App\Models\PlatformSetting;
use App\Services\ProductService;
use App\Services\BarcodeLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TenantProductController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('tenant_id', tenant()->getTenantKey())],
            'stock_status' => ['nullable', 'in:all,in_stock,low_stock,out_of_stock'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;
        $stockStatus = $filters['stock_status'] ?? 'all';
        $products = Product::query()
            ->with(['category', 'inventoryStock'])
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($productQuery) use ($search): void {
                    $productQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
            ->when($stockStatus === 'in_stock', fn ($query) => $query->whereHas('inventoryStock', fn ($stockQuery) => $stockQuery->where('quantity', '>', 0)))
            ->when($stockStatus === 'low_stock', fn ($query) => $query->whereHas('inventoryStock', fn ($stockQuery) => $stockQuery->where('quantity', '>', 0)->whereColumn('inventory_stocks.quantity', '<=', 'inventory_stocks.low_stock_threshold')))
            ->when($stockStatus === 'out_of_stock', fn ($query) => $query->where(fn ($stockQuery) => $stockQuery
                ->whereDoesntHave('inventoryStock')
                ->orWhereHas('inventoryStock', fn ($inventoryQuery) => $inventoryQuery->where('quantity', '<=', 0))));

        $summary = (clone $products)
            ->leftJoin('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->selectRaw('COUNT(DISTINCT products.id) as product_count')
            ->selectRaw('COALESCE(SUM(COALESCE(inventory_stocks.quantity, 0)), 0) as units_in_stock')
            ->selectRaw('COALESCE(SUM(COALESCE(inventory_stocks.quantity, 0) * COALESCE(products.cost_minor, 0)), 0) as cost_value_minor')
            ->selectRaw('COALESCE(SUM(COALESCE(inventory_stocks.quantity, 0) * products.price_minor), 0) as retail_value_minor')
            ->selectRaw('COALESCE(SUM(CASE WHEN products.cost_minor IS NOT NULL THEN COALESCE(inventory_stocks.quantity, 0) * (products.price_minor - products.cost_minor) ELSE 0 END), 0) as profit_value_minor')
            ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(inventory_stocks.quantity, 0) > 0 AND COALESCE(inventory_stocks.quantity, 0) <= COALESCE(inventory_stocks.low_stock_threshold, 0) THEN 1 ELSE 0 END), 0) as low_stock_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(inventory_stocks.quantity, 0) <= 0 THEN 1 ELSE 0 END), 0) as out_of_stock_count')
            ->first();

        return Inertia::render('Tenant/Products/Index', [
            'products' => $products->latest()->paginate(20)->withQueryString(),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'catalogueMode' => TenantSetting::query()->where('key', 'catalogue_mode')->value('value') ?? PlatformSetting::value('catalogue_mode_default', 'shared'),
            'filters' => ['search' => $search, 'category_id' => $categoryId, 'stock_status' => $stockStatus],
            'summary' => [
                'product_count' => (int) ($summary->product_count ?? 0),
                'units_in_stock' => (int) ($summary->units_in_stock ?? 0),
                'cost_value_minor' => (int) ($summary->cost_value_minor ?? 0),
                'retail_value_minor' => (int) ($summary->retail_value_minor ?? 0),
                'profit_value_minor' => (int) ($summary->profit_value_minor ?? 0),
                'low_stock_count' => (int) ($summary->low_stock_count ?? 0),
                'out_of_stock_count' => (int) ($summary->out_of_stock_count ?? 0),
            ],
        ]);
    }

    public function store(
        StoreProductRequest $request,
        ProductService $productService,
    ): RedirectResponse {
        $productService->create($request);

        return back()->with('success', 'Product created successfully.');
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductService $productService,
    ): RedirectResponse {
        $productService->update($product, $request);

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(
        Product $product,
        ProductService $productService,
    ): RedirectResponse {
        $productService->archive($product);

        return back()->with('success', 'Product removed from the active catalogue.');
    }

    public function media(string $path): BinaryFileResponse
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        return response()->file(Storage::disk('public')->path($path));
    }

    public function barcodeLookup(
        string $barcode,
        BarcodeLookupService $barcodeLookupService,
    ): JsonResponse {
        abort_unless(preg_match('/^[0-9A-Za-z-]{3,80}$/', $barcode) === 1, 422);

        return response()->json($barcodeLookupService->lookup($barcode));
    }
}
