<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class TenantSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];
}
