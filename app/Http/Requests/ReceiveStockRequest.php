<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveStockRequest extends FormRequest
{
    public function authorize(): bool { return tenant() !== null; }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost_minor' => ['required', 'integer', 'min:0'],
            'purchased_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}