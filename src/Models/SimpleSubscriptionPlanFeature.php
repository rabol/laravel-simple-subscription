<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rabol\SimpleSubscription\Services\SimpleSubscriptionPeriod;
use Rabol\SimpleSubscription\Traits\BelongsToPlan;

class SimpleSubscriptionPlanFeature extends Model
{
    use BelongsToPlan;

    protected $table = 'ss_plan_features';

    protected $fillable = [
        'plan_id',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'sort_order',
    ];

    protected $casts = [
        'plan_id' => 'integer',
        'name' => 'string',
        'value' => 'integer',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function usage(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanSubscriptionUsage::class, 'feature_id', 'id');
    }

    public function getResetDate(Carbon $dateFrom): Carbon
    {
        $period = new SimpleSubscriptionPeriod($this->resettable_interval, $this->resettable_period, $dateFrom);

        return $period->getEndDate();
    }
}
