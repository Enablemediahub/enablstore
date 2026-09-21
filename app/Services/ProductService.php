<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\InventoryStock;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create a product and its initial inventory record atomically.
     */
    public function create(StoreProductRequest $request): Product
    {
        return DB::transaction(function () use ($request): Product {
            $imagePath = $request->hasFile('image')
                ? 'storage/'.Storage::disk('public')->putFile(
                    'products/'.tenant()->getTenantKey(),
                    $request->file('image'),
                )
                : null;
            $gallery = $this->storeGallery($request);
            $imagePath ??= $gallery[0] ?? null;

            $product = Product::query()->create([
                'name' => $request->string('name')->toString(),
                'slug' => $this->resolveSlug($request->string('name')->toString()),
                'sku' => $this->resolveSku($request->input('sku'), $request->string('name')->toString()),
                'barcode' => $request->input('barcode'),
                'image_path' => $imagePath,
                'image_gallery' => $gallery,
                'price_minor' => $request->integer('price_minor'),
                'cost_minor' => $request->has('cost_minor') ? $request->integer('cost_minor') : null,
                'purchase_unit' => $request->string('purchase_unit')->toString(),
                'units_per_purchase' => $request->integer('units_per_purchase'),
                'category_id' => $request->input('category_id'),
                'description' => $request->input('description'),
                'currency' => 'GHS',
                'is_active' => true,
                'available_in_pos' => $request->boolean('available_in_pos'),
                'available_online' => $request->boolean('available_online'),
                'is_online_deal' => $request->boolean('is_online_deal'),
                'compare_at_price_minor' => $request->filled('compare_at_price_minor')
                    ? $request->integer('compare_at_price_minor')
                    : null,
            ]);

            InventoryStock::query()->create([
                'product_id' => $product->id,
                'quantity' => $request->integer('initial_quantity'),
                'low_stock_threshold' => $request->integer('low_stock_threshold'),
            ]);

            return $product;
        });
    }

    public function update(Product $product, UpdateProductRequest $request): Product
    {
        return DB::transaction(function () use ($product, $request): Product {
            $imagePath = $product->image_path;

            if ($request->hasFile('image')) {
                if ($imagePath !== null) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $imagePath));
                }

                $imagePath = 'storage/'.Storage::disk('public')->putFile(
                    'products/'.tenant()->getTenantKey(),
                    $request->file('image'),
                );
            }

            $gallery = $product->image_gallery ?? [];
            $gallery = array_merge($gallery, $this->storeGallery($request));
            if ($imagePath === null) {
                $imagePath = $gallery[0] ?? null;
            }

            $product->update([
                'name' => $request->string('name')->toString(),
                'slug' => $this->resolveSlug($request->string('name')->toString(), $product->id),
                'sku' => $this->resolveSku($request->input('sku'), $request->string('name')->toString()),
                'barcode' => $request->input('barcode'),
                'image_path' => $imagePath,
                'image_gallery' => $gallery,
                'price_minor' => $request->integer('price_minor'),
                'cost_minor' => $request->has('cost_minor') ? $request->integer('cost_minor') : null,
                'purchase_unit' => $request->string('purchase_unit')->toString(),
                'units_per_purchase' => $request->integer('units_per_purchase'),
                'category_id' => $request->input('category_id'),
                'available_in_pos' => $request->boolean('available_in_pos'),
                'available_online' => $request->boolean('available_online'),
                'is_online_deal' => $request->boolean('is_online_deal'),
                'compare_at_price_minor' => $request->filled('compare_at_price_minor')
                    ? $request->integer('compare_at_price_minor')
                    : null,
            ]);

            $product->inventoryStock()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => $request->integer('initial_quantity'),
                    'low_stock_threshold' => $request->integer('low_stock_threshold'),
                ],
            );

            return $product->fresh();
        });
    }

    public function archive(Product $product): void
    {
        $product->update(['is_active' => false]);
    }

    private function resolveSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, static fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function storeGallery(StoreProductRequest|UpdateProductRequest $request): array
    {
        return collect($request->file('images', []))->map(fn ($image) => 'storage/'.Storage::disk('public')->putFile(
            'products/'.tenant()->getTenantKey(),
            $image,
        ))->values()->all();
    }

    private function resolveSku(?string $sku, string $name): string
    {
        if ($sku !== null && trim($sku) !== '') {
            return trim($sku);
        }

        return Str::upper(Str::slug($name, '-')).'-'.Str::upper(Str::random(6));
    }
}
