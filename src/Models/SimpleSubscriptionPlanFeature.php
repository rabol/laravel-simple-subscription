<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimpleSubscriptionPlanFeature extends Model
{
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function usage(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanFeature::class, 'feature_id', 'id');
    }
}
