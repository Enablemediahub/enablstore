<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);
        static::creating(static function (Model $model): void {
            $tenantId = tenant()?->getTenantKey();
            if ($tenantId === null) {
                throw new LogicException('Tenant-owned records must be created within a tenant context.');
            }

            if ($model->getAttribute('tenant_id') !== null && $model->getAttribute('tenant_id') !== $tenantId) {
                throw new LogicException('Tenant-owned records cannot be created for another tenant.');
            }

            $model->setAttribute('tenant_id', $tenantId);
        });
    }
}