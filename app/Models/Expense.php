<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
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