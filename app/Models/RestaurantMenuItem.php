<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToTenant;

class RestaurantMenuItem extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'category', 'description', 'price_minor', 'unit_label', 'image_path', 'is_available', 'option_groups'];

    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'is_available' => 'boolean',
            'option_groups' => 'array',
        ];
    }

    /** @return HasMany<RestaurantOrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class, 'menu_item_id');
    }
}