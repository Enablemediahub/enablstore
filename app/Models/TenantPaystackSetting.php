<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPaystackSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'public_key',
        'secret_key',
        'test_public_key',
        'test_secret_key',
        'mode',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'secret_key' => 'encrypted',
            'test_secret_key' => 'encrypted',
            'enabled' => 'boolean',
        ];
    }

    public function getConnectionName(): ?string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }
}