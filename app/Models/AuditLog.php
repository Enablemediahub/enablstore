<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class AuditLog extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'user_id', 'action', 'auditable_type', 'auditable_id', 'metadata', 'ip_address'];

    protected $casts = ['metadata' => 'array'];
}