<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimpleSubscriptionPlanSubscriptionUsage extends Model
{
    protected $table = 'ss_plan_subscription_usages';

    protected $fillable = [
        'subscription_id',
        'feature_id',
        'used',
        'valid_until',
    ];

    protected $casts = [
        'subscription_id' => 'integer',
        'feature_id' => 'integer',
        'used' => 'integer',
        'valid_until' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(
            SimpleSubscriptionPlanFeature::class,
            'feature_id',
            'id',
            'feature'
        );
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            SimpleSubscriptionPlanSubscription::class,
            'subscription_id',
            'id',
            'subscription'
        );
    }

    public function expired(): bool
    {
        if (is_null($this->valid_until)) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
}
