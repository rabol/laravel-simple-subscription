<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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

    public function subscription(string $subscriptionSlug): ?SimpleSubscriptionPlanSubscription
    {
        return $this->subscriptions()->where('slug', $subscriptionSlug)->first();
    }

    public function subscribedPlans(): ?SimpleSubscriptionPlanSubscription
    {
        $planIds = $this->subscriptions->reject->inactive()->pluck('plan_id')->unique();

        return app('rinvex.subscriptions.plan')->whereIn('id', $planIds)->get();
    }

    public function subscribedTo($planId): bool
    {
        $subscription = $this->subscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    public function newSubscription($subscription, Plan $plan, Carbon $startDate = null): SimpleSubscriptionPlanSubscription
    {
        $trial = new SimpleSubscriptionPeriod($plan->trial_interval, $plan->trial_period, $startDate ?? now());
        $period = new SimpleSubscriptionPeriod($plan->invoice_interval, $plan->invoice_period, $trial->getEndDate());

        return $this->subscriptions()->create([
            'name' => $subscription,
            'plan_id' => $plan->getKey(),
            'trial_ends_at' => $trial->getEndDate(),
            'starts_at' => $period->getStartDate(),
            'ends_at' => $period->getEndDate(),
        ]);
    }
}
