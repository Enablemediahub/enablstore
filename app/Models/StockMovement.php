<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class StockMovement extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'product_id', 'supplier_id', 'type', 'quantity', 'unit_cost_minor', 'purchased_at', 'note'];

    protected $casts = ['quantity' => 'integer', 'unit_cost_minor' => 'integer', 'purchased_at' => 'date'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
}