<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'transaction_uuid',
        'subtotal_minor',
        'discount_minor',
        'discount_type',
        'discount_reason',
        'total_minor',
        'currency',
        'payment_method',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'subtotal_minor' => 'integer',
        'discount_minor' => 'integer',
        'total_minor' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
