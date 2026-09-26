<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPaymentIntent extends Model
{
    protected $fillable = [
        'tenant_id',
        'reference',
        'context',
        'amount_minor',
        'payload',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function getConnectionName(): ?string
    {
        return config('tenancy.database.central_connection', config('database.default'));
    }
}