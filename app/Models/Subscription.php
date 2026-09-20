<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'provider',
        'provider_reference',
        'status',
        'starts_at',
        'renews_at',
        'ends_at',
        'grace_ends_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'renews_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
