<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rabol\SimpleSubscription\Services\SimpleSubscriptionPeriod;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SimpleSubscriptionPlanFeature extends Model
{
    use HasSlug;

    protected $table = 'ss_plan_features';

    protected $fillable = [
        'plan_id',
        'slug',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'sort_order',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'plan_id' => 'integer',
        'slug' => 'string',
        'name' => 'strigng',
        'value' => 'string',
        'resettable_period' => 'integer',
        'resettable_interval' => 'string',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function usage(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanFeature::class, 'feature_id', 'id');
    }

      public function getResetDate(Carbon $dateFrom): Carbon
    {
        $period = new SimpleSubscriptionPeriod($this->resettable_interval, $this->resettable_period, $dateFrom ?? now());

        return $period->getEndDate();
    }
}
