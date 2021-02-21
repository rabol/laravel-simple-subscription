<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rabol\SimpleSubscription\Services\SimpleSubscriptionPeriod;
use Carbon\Carbon;
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
        'value' => 'string',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function usage(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanSubscriptionUsage::class, 'feature_id', 'id');
    }

    /**
     * Get feature's reset date.
     *
     * @param string $dateFrom
     *
     * @return \Carbon\Carbon
     */
    public function getResetDate(Carbon $dateFrom): Carbon
    {
        $period = new SimpleSubscriptionPeriod($this->resettable_interval, $this->resettable_period, $dateFrom ?? now());

        return $period->getEndDate();
    }
}
