<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    protected $fillable = [
        'method',
        'amount_minor',
        'cash_received_minor',
        'externally_confirmed',
        'provider_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'cash_received_minor' => 'integer',
            'externally_confirmed' => 'boolean',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}