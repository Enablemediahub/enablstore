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
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TenantProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('Tenant/Products/Index', [
            'products' => Product::query()
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
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'catalogueMode' => TenantSetting::query()->where('key', 'catalogue_mode')->value('value') ?? PlatformSetting::value('catalogue_mode_default', 'shared'),
            'filters' => ['search' => $search],
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
