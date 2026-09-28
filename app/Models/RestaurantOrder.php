<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToTenant;

class RestaurantOrder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'table_label',
        'customer_name',
        'customer_phone',
        'customer_phone',
        'notes',
        'status',
        'total_minor',
        'created_by_name',
    ];

    protected function casts(): array
    {
        return ['total_minor' => 'integer'];
    }

    /** @return HasMany<RestaurantOrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class, 'restaurant_order_id');
    }
}