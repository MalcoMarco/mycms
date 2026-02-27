<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (tenancy()->initialized) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', tenant()->getTenantKey());
            }
        });

        static::creating(function (Model $model) {
            if (tenancy()->initialized && ! $model->tenant_id) {
                $model->tenant_id = tenant()->getTenantKey();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}