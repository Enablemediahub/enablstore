<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToTenant;

class Supplier extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'contact_name', 'phone', 'email', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}