<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Rabol\SimpleSubscription\Services\SimpleSubscriptionPeriod;
use Rabol\SimpleSubscription\Traits\BelongsToPlan;

class SimpleSubscriptionPlanSubscription extends Model
{
    use BelongsToPlan;

    protected $table = 'ss_plan_subscriptions';

    protected $fillable = [
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'name',
        'description',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancels_at',
        'canceled_at',
    ];

    protected $casts = [
        'subscriber_id' => 'integer',
        'subscriber_type' => 'string',
        'plan_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancels_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function subscriber(): MorphTo
    {
        return $this->morphTo(
            'subscriber',
            'subscriber_type',
            'subscriber_id',
            'id'
        );
    }

    public function usage(): hasMany
    {
        return $this->hasMany(
            SimpleSubscriptionPlanSubscriptionUsage::class,
            'subscription_id',
            'id'
        );
    }

    public function active(): bool
    {
        return ! $this->ended() || $this->onTrial();
    }

    public function inactive(): bool
    {
        return ! $this->active();
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at ? Carbon::now()->lt($this->trial_ends_at) : false;
    }

    public function canceled(): bool
    {
        return $this->canceled_at ? Carbon::now()->gte($this->canceled_at) : false;
    }

    public function ended(): bool
    {
        return $this->ends_at ? Carbon::now()->gte($this->ends_at) : false;
    }

    public function cancel($immediately = false): SimpleSubscriptionPlanSubscription
    {
        $this->canceled_at = Carbon::now();

        if ($immediately) {
            $this->ends_at = $this->canceled_at;
        }

        // Set the date for which this is valid
        $this->cancels_at = $this->ends_at;

        $this->save();

        return $this;
    }

    public function changePlan(SimpleSubscriptionPlan $plan): SimpleSubscriptionPlanSubscription
    {
        // If plans does not have the same billing frequency
        // (e.g., invoice_interval and invoice_period) we will update
        // the billing dates starting today, and sice we are basically creating
        // a new billing cycle, the usage data will be cleared.
        if ($this->plan->invoice_interval !== $plan->invoice_interval || $this->plan->invoice_period !== $plan->invoice_period) {
            $this->setNewPeriod($plan->invoice_interval, $plan->invoice_period, Carbon::now());
            $this->usage()->delete();
        }

        // Attach new plan to subscription
        $this->plan_id = $plan->id;
        $this->save();

        return $this;
    }

    public function canRenew()
    {
        if ($this->ended() && $this->canceled()) {
            return false;
        }

        return true;
    }

    /**
     * Renew subscription period.
     *
     * @throws \LogicException
     *
     * @return $this
     */
    public function renew()
    {
        if ($this->ended() && $this->canceled()) {
            throw new \LogicException(__('Unable to renew canceled ended subscription.'));
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription) {
            // Clear usage data
            $subscription->usage()->delete();

            // Renew period
            $subscription->setNewPeriod($this->plan->invoice_interval, $this->plan->invoice_period, Carbon::now());
            $subscription->canceled_at = null;
            $subscription->cancels_at = null;
            $subscription->save();
        });

        return $this;
    }

    public function scopeOfSubscriber(Builder $builder, Model $subscriber): Builder
    {
        return $builder->where(
            'subscriber_type',
            $subscriber->getMorphClass()
        )->where('subscriber_id', $subscriber->getKey());
    }

    public function scopeFindEndingTrial(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('trial_ends_at', [$from, $to]);
    }

    public function scopeFindEndedTrial(Builder $builder): Builder
    {
        return $builder->where('trial_ends_at', '<=', now());
    }

    public function scopeFindEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('ends_at', [$from, $to]);
    }

    public function scopeFindEndedPeriod(Builder $builder): Builder
    {
        return $builder->where('ends_at', '<=', now());
    }

    protected function setNewPeriod(?string $invoice_interval, ?int $invoice_period, ?Carbon $start): self
    {
        if (is_null($invoice_interval)) {
            $invoice_interval = $this->plan->invoice_interval;
        }

        if (is_null($invoice_period)) {
            $invoice_period = $this->plan->invoice_period;
        }

        $period = new SimpleSubscriptionPeriod($invoice_interval, $invoice_period, $start);

        $this->starts_at = $period->getStartDate();
        $this->ends_at = $period->getEndDate();

        return $this;
    }

    public function recordFeatureUsage(string $featureName, int $uses = 1, bool $incremental = true): ?SimpleSubscriptionPlanSubscriptionUsage
    {
        $feature = $this->plan->features()->whereName($featureName)->first();
        if ($feature) {
            return $this->recordFeatureUsageId($feature->id, $uses, $incremental);
        }

        return null;
    }

    public function recordFeatureUsageId(int $featureId, int $uses = 1, bool $incremental = true): SimpleSubscriptionPlanSubscriptionUsage
    {
        $feature = $this->plan->features()->whereId($featureId)->first();

        $usage = $this->usage()->firstOrNew([
            'subscription_id' => $this->id,
            'feature_id' => $feature->id,
            'used' => 0,
        ]);

        if ($feature->resettable_period) {
            // Set expiration date when the usage record is new or doesn't have one.
            if (is_null($usage->valid_until)) {
                // Set date from subscription creation date so the reset
                // period match the period specified by the subscription's plan.
                $usage->valid_until = $feature->getResetDate($this->created_at);
            } elseif ($usage->expired()) {
                // If the usage record has been expired, let's assign
                // a new expiration date and reset the uses to zero.
                $usage->valid_until = $feature->getResetDate($usage->valid_until);
                $usage->used = 0;
            }
        }

        $usage->used = ($incremental ? $usage->used + $uses : $uses);

        if ($usage->used >= 0) {
            $usage->save();
        }

        return $usage;
    }

    public function increaseFeatureUsage(string $featureName, int $uses = 1): ?SimpleSubscriptionPlanSubscriptionUsage
    {
        $feature = $this->plan->features()->whereName($featureName)->first();
        if ($feature) {
            return $this->increaseFeatureUsageId($feature->id, $uses);
        }

        return null;
    }

    public function increaseFeatureUsageId(int $featureId, int $uses = 1): ?SimpleSubscriptionPlanSubscriptionUsage
    {
        $usage = $this->usage()->whereFeatureId($featureId)->whereSubscriptionId($this->id)->first();

        if (is_null($usage)) {
            $usage = SimpleSubscriptionPlanSubscriptionUsage::create([
                'subscription_id' => $this->id,
                'feature_id' => $featureId,
                'used' => 0,
            ]);
        }

        $usage->used = $usage->used + $uses;

        if ($usage->used >= 0) {
            $usage->update();
        }

        return $usage;
    }

    public function reduceFeatureUsage(string $featureName, int $uses = 1): ?SimpleSubscriptionPlanSubscriptionUsage
    {
        $feature = SimpleSubscriptionPlanFeature::where([
            ['plan_id',$this->plan_id],
            ['name', $featureName],
        ])->first();

        if ($feature) {
            return $this->reduceFeatureUsageId($feature->id, $uses);
        }

        return null;
    }

    public function reduceFeatureUsageId(int $featureId, int $uses = 1): ?SimpleSubscriptionPlanSubscriptionUsage
    {
        $usage = $this->usage()->whereFeatureId($featureId)->whereSubscriptionId($this->id)->first();

        if (is_null($usage)) {
            $usage = SimpleSubscriptionPlanSubscriptionUsage::create([
                'subscription_id' => $this->id,
                'feature_id' => $featureId,
                'used' => 0,
            ]);
        }

        $usage->used = $usage->used - $uses;

        if ($usage->used >= 0) {
            $usage->update();
        }

        return $usage;
    }

    public function canUseFeature(string $featureName): bool
    {
        $remaining = $this->getFeatureRemaining($featureName);

        return  ($remaining != 0 && $remaining >= 0) ;
    }

    public function canUseFeatureId(int $featureId): bool
    {
        return $this->getFeatureRemainingId($featureId) != 0;
    }

    public function getFeatureUsage(string $featureName): int
    {
        $feature = SimpleSubscriptionPlanFeature::where([
            ['plan_id', $this->plan_id],
            ['name',$featureName],
        ])->first();

        if (! $feature) {
            return 0;
        }

        $usage = SimpleSubscriptionPlanSubscriptionUsage::where(
            [
                ['subscription_id', $this->id],
                ['feature_id', $feature->id],
            ]
        )->first();

        return $usage->used ?? 0;
    }

    public function getFeatureUsageId(int $featureId): int
    {
        $usage = $this->usage()->whereFeatureId($featureId)->first();

        return $usage->used ?? 0;
    }

    public function getFeatureRemaining(string $featureName): int
    {
        return $this->getFeatureValue($featureName) - $this->getFeatureUsage($featureName);
    }

    public function getFeatureRemainingId(int $featureId): int
    {
        return $this->getFeatureValueId($featureId) - $this->getFeatureUsageId($featureId);
    }

    public function getFeatureValue(string $featureName): int
    {
        $feature = SimpleSubscriptionPlanFeature::where([
            ['plan_id', $this->plan_id],
            ['name',$featureName],
        ])->first();

        return $feature->value ?? 0;
    }

    public function getFeatureValueId(int $featureId): int
    {
        $feature = $this->plan->features()->whereId($featureId)->first();

        return $feature->value ?? 0;
    }
}
