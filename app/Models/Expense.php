<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Expense extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category',
        'description',
        'amount_minor',
        'currency',
        'payment_method',
        'spent_at',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'spent_at' => 'datetime',
    ];
}