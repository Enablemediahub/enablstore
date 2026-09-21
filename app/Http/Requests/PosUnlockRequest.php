<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosUnlockRequest extends FormRequest
{
    public function authorize(): bool { return tenant() !== null; }

    public function rules(): array
    {
        return ['username' => ['required', 'string', 'max:60'], 'pin' => ['required', 'digits_between:4,6']];
    }
}
