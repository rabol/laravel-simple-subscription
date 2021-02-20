<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimpleSubscriptionPlan extends Model
{
    use HasSlug;

    protected $table = 'ss_plans';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'price',
        'signup_fee',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
        'prorate_day',
        'prorate_period',
        'prorate_extend_due',
        'active_subscribers_limit',
        'sort_order',
   ];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'is_active' => 'boolean',
        'price' => 'float',
        'signup_fee' => 'float',
        'currency' => 'string',
        'trial_period' => 'integer',
        'trial_interval' => 'string',
        'invoice_period' => 'integer',
        'invoice_interval' => 'string',
        'grace_period' => 'integer',
        'grace_interval' => 'string',
        'prorate_day' => 'integer',
        'prorate_period' => 'integer',
        'prorate_extend_due' => 'integer',
        'active_subscribers_limit' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function features(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanFeature::class, 'plan_id', 'id');
    }

    /**
     * The plan may have many subscriptions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanSubscription::class, 'simple_subscription_plan_id', 'id');
    }

    /**
     * Check if plan is free.
     *
     * @return bool
     */
    public function isFree(): bool
    {
        return (float) $this->price <= 0.00;
    }

    /**
     * Check if plan has trial.
     *
     * @return bool
     */
    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    /**
     * Check if plan has grace.
     *
     * @return bool
     */
    public function hasGrace(): bool
    {
        return $this->grace_period && $this->grace_interval;
    }

    public function getFeatureBySlug(string $slug): ?SimpleSubscriptionPlanFeature
    {
        return $this->features()->whereSlog($slug)->first();
    }

    /**
     * Activate the plan.
     *
     * @return $this
     */
    public function activate()
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    /**
     * Deactivate the plan.
     *
     * @return $this
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
