<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class SaleItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'product_id',
        'restaurant_menu_item_id',
        'item_name',
        'quantity',
        'unit_price_minor',
        'line_total_minor',
        'selected_options',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_minor' => 'integer',
        'line_total_minor' => 'integer',
        'selected_options' => 'array',
    ];

    /**
     * @return BelongsTo<Sale, $this>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<RestaurantMenuItem, $this>
     */
    public function restaurantMenuItem(): BelongsTo
    {
        return $this->belongsTo(RestaurantMenuItem::class);
    }
}
