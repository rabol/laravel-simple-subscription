<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rabol\SimpleSubscription\Models\SimpleSubscriptionPlan;
use Rabol\SimpleSubscription\Models\SimpleSubscriptionPlanSubscription;
use Rabol\SimpleSubscription\Services\SimpleSubscriptionPeriod;

trait HasSubscriptions
{
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(SimpleSubscriptionPlanSubscription::class, 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    public function activeSubscriptions(): Collection
    {
        return $this->subscriptions->reject->inactive();
    }

    public function subscription(string $subscriptionName): ?SimpleSubscriptionPlanSubscription
    {
        return $this->subscriptions()->whereName($subscriptionName)->first();
    }

    public function subscribedPlans(): ?Collection
    {
        $planIds = $this->subscriptions->reject->inactive()->pluck('plan_id')->unique();

        return SimpleSubscriptionPlanSubscription::whereIn('id', $planIds)->get();
    }

    public function subscribedToPlanName(string $planName): bool
    {
        $planId = SimpleSubscriptionPlan::whereName($planName)->first()->id;

        $subscription = $this->subscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    public function subscribedToPlanId($planId): bool
    {
        $subscription = $this->subscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    public function newSubscription(SimpleSubscriptionPlan $plan, Carbon $startDate = null): SimpleSubscriptionPlanSubscription
    {
        if (is_null($startDate)) {
            $startDate = Carbon::now();
        }

        $trial = new SimpleSubscriptionPeriod($plan->trial_interval, $plan->trial_period, $startDate);
        $period = new SimpleSubscriptionPeriod($plan->invoice_interval, $plan->invoice_period, $trial->getEndDate());

        return $this->subscriptions()->create([
            'name' => $plan->name,
            'description' => $plan->description,
            'plan_id' => $plan->id,
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate(),
        ]);
    }
}
