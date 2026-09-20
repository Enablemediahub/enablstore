<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosUnlockRequest extends FormRequest
{
    public function authorize(): bool { return tenant() !== null; }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'pin' => ['required', 'digits_between:4,6']];
    }
}