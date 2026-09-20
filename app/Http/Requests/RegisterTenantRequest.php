<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! $this->user();
    }

    /**
    * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'alpha_dash', 'min:3', 'max:60'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'owner_name' => ['required', 'string', 'max:120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => strtolower((string) $this->input('slug')),
        ]);
    }
}