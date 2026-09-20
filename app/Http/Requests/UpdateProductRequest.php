<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return tenant() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:160'],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('products', 'barcode')->ignore($product->id)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'price_minor' => ['required', 'integer', 'min:0'],
            'cost_minor' => ['nullable', 'integer', 'min:0'],
            'purchase_unit' => ['required', 'string', 'max:40'],
            'units_per_purchase' => ['required', 'integer', 'min:1'],
            'initial_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'available_in_pos' => ['required', 'boolean'],
            'available_online' => ['required', 'boolean'],
        ];
    }
}