<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BarcodeLookupService
{
    /**
     * @return array{found: bool, message: string|null, product: array<string, mixed>|null}
     */
    public function lookup(string $barcode): array
    {
        $key = config('services.barcode_lookup.key');

        if (! is_string($key) || trim($key) === '') {
            return [
                'found' => false,
                'message' => 'Barcode lookup is not configured. Enter the product details manually.',
                'product' => null,
            ];
        }

        $response = Http::timeout(8)->get(config('services.barcode_lookup.url'), [
            'barcode' => $barcode,
            'formatted' => 'y',
            'key' => $key,
        ]);

        if (! $response->successful()) {
            return [
                'found' => false,
                'message' => 'The barcode service is temporarily unavailable. Enter the product details manually.',
                'product' => null,
            ];
        }

        $product = $response->json('products.0');

        if (! is_array($product)) {
            return [
                'found' => false,
                'message' => 'No product was found for this barcode. Enter the details manually.',
                'product' => null,
            ];
        }

        return [
            'found' => true,
            'message' => 'Product details found. Please review them before saving.',
            'product' => [
                'name' => $product['title'] ?? $product['product_name'] ?? null,
                'barcode' => $barcode,
                'description' => $product['description'] ?? null,
                'category' => $product['category'] ?? null,
                'brand' => $product['brand'] ?? null,
                'images' => is_array($product['images'] ?? null)
                    ? array_values(array_filter($product['images']))
                    : [],
            ],
        ];
    }
}