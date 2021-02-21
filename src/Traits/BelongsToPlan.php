<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rabol\SimpleSubscription\Models\SimpleSubscriptionPlan;

trait BelongsToPlan
{
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SimpleSubscriptionPlan::class, 'plan_id', 'id', 'plan');
    }

    public function scopeByPlanId(Builder $builder, int $planId): Builder
    {
        return $builder->where('plan_id', $planId);
    }
}
