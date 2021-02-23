<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimpleSubscriptionPlan extends Model
{
    protected $table = 'ss_plans';

    protected $fillable = [
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
        'plan_type',
   ];

    protected $casts = [
        'name' => 'string',
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
        'plan_typer' => 'integer',
        
    ];

    public function features(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanFeature::class, 'plan_id', 'id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(SimpleSubscriptionPlanSubscription::class, 'plan_id', 'id');
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0.00;
    }

    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }

    public function hasGrace(): bool
    {
        return $this->grace_period && $this->grace_interval;
    }

    public function activate(): self
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    public function deactivate(): self
    {
        $this->update(['is_active' => false]);

        return $this;
    }

    public function getFeatureByName(string $featureName): ?SimpleSubscriptionPlanFeature
    {
        return $this->features()->where('name', $featureName)->first();
    }
}
