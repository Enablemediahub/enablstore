<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToTenant;

class Sale extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'transaction_uuid',
        'cashier_name',
        'customer_name',
        'customer_phone',
        'customer_email',
        'subtotal_minor',
        'discount_minor',
        'discount_type',
        'discount_reason',
        'total_minor',
        'currency',
        'payment_method',
        'source',
        'delivery_location',
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

    /**
     * @return HasMany<SalePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }
}
