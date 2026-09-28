<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class RestaurantOrderItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'restaurant_menu_item_id',
        'item_name',
        'quantity',
        'unit_price_minor',
        'line_total_minor',
        'selected_options',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_minor' => 'integer',
            'line_total_minor' => 'integer',
            'selected_options' => 'array',
        ];
    }

    /** @return BelongsTo<RestaurantOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class, 'restaurant_order_id');
    }

    /** @return BelongsTo<RestaurantMenuItem, $this> */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(RestaurantMenuItem::class, 'restaurant_menu_item_id');
    }
}