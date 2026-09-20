<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncOfflineSalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return tenant() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'sales' => ['required', 'array', 'max:100'],
            'sales.*.transaction_uuid' => ['required', 'uuid'],
            'sales.*.payment_method' => ['required', 'in:cash,mobile_money,card'],
            'sales.*.items' => ['required', 'array', 'min:1'],
            'sales.*.items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'sales.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'sales.*.discount_type' => ['nullable', 'in:fixed,percentage'],
            'sales.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'sales.*.discount_reason' => ['nullable', 'string', 'max:120'],
        ];
    }
}
