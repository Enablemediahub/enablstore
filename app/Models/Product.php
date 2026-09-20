<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'price_minor',
        'cost_minor',
        'currency',
        'description',
        'image_path',
        'image_gallery',
        'is_active',
        'available_in_pos',
        'available_online',
    ];

    protected $casts = [
        'price_minor' => 'integer',
        'cost_minor' => 'integer',
        'is_active' => 'boolean',
        'available_in_pos' => 'boolean',
        'available_online' => 'boolean',
        'image_gallery' => 'array',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasOne<InventoryStock, $this>
     */
    public function inventoryStock(): HasOne
    {
        return $this->hasOne(InventoryStock::class);
    }
}
