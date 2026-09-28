<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'transaction_uuid' => ['required', 'uuid'],
            'payment_method' => ['required', 'in:cash,mobile_money,card,split'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('tenant_id', tenant()->getTenantKey())],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'tenders' => ['sometimes', 'required', 'array', 'min:1', 'max:3'],
            'tenders.*.method' => ['required_with:tenders', 'in:cash,mobile_money,card'],
            'tenders.*.amount_minor' => ['required_with:tenders', 'integer', 'min:1'],
            'tenders.*.cash_received_minor' => ['nullable', 'integer', 'min:0'],
            'tenders.*.externally_confirmed' => ['nullable', 'boolean'],
            'discount_type' => ['nullable', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:120'],
        ];
    }
}
