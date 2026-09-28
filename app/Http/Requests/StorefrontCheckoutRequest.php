<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Controllers\TenantSettingsController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorefrontCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return tenant() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $locations = TenantSettingsController::storefrontConfigForStorefront($this)['deliveryLocations'];

        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'delivery_location' => ['required', 'string', Rule::in($locations)],
            'payment_method' => ['required', 'in:card,mobile_money'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('tenant_id', tenant()->getTenantKey())],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}