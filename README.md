# Simple subscription package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rabol/laravel-simple-subscription.svg?style=flat-square)](https://packagist.org/packages/rabol/laravel-simple-subscription)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/rabol/laravel-simple-subscription/run-tests?label=tests)](https://github.com/rabol/laravel-simple-subscription/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/rabol/laravel-simple-subscription/Check%20&%20fix%20styling?label=code%20style)](https://github.com/rabol/laravel-simple-subscription/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/rabol/laravel-simple-subscription.svg?style=flat-square)](https://packagist.org/packages/rabol/laravel-simple-subscription)


This is a simple to use subscription package for Laravel.

It is heavvly inspired by the renvex/laravel-subscriptions package, just simpler and working :)

Sorry, My point is that the renvex packages seems to be abandond.

## Payments

- Payment support is in the works, so stay tuned.
- We will most likley use laraveldaily/laravel-invoices and then a payment package


## Installation

You can install the package via composer:

```bash
composer require rabol/laravel-simple-subscription
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Rabol\SimpleSubscription\SimpleSubscriptionServiceProvider" --tag="laravel-simple-subscription-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Rabol\SimpleSubscription\SimpleSubscriptionServiceProvider" --tag="laravel-simple-subscription-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

### Add Subscriptions to your model

For the sake of simplicity there is a trait that can be added to any model.

The most common use case is to add Subscription functionality to your User model just use the `Rabol\SimpleSubscription\Traits\HasSubscriptions` trait like this:

```php
namespace App\Models;

use Rabol\SimpleSubscription\Traits\HasSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

That's it, now you can use subscriptions on your user model only have to use that trait in our User model! Now your users may subscribe to plans.

## Create a new Plan

```php
$newPlan = SimpleSubscriptionPlan::create([
    'name' => 'My cool plan',
    'description' => 'This is a very cool plan',
    'is_active' => true,
    'price' => 12.50,
    'signup_fee' => 0,
    'currency' => 'eur',
    'trial_period'  => 1,
    'trial_interval' => 'week',
    'invoice_period' => 1,
    'invoice_interval' => 'month',
    'grace_period' => 3,
    'grace_interval' => 'day',
]);
````

Add a feature

```php

$planFeature = $newPlan
    ->features()
    ->create([
        'name' => 'My cool feature',
        'description' => 'This is my cool feature',
        'value' => 100,
        'resettable_period' => 2,
        'resettable_interval' => 'month',
        'sort_order' => 1,
    ]);
```

## Subscription plan details

```php
$plan = SimpleSubscriptionPlan::find(1);
or
$plan = SimpleSubscriptionPlan::whereName('My cool subscription')->first();

// Get all plan features                
$plan->features;

// Get all plan subscriptions
$plan->subscriptions;

// Check if the plan is free
$plan->isFree();

// Check if the plan has trial period
$plan->hasTrial();

// Check if the plan has grace period
$plan->hasGrace();
```

## Get Feature Value
There are different ways to get information about a feature on a plan
To get the value of the feature 'My cool feature'.:

```php
// Use the plan instance to get feature's value
$myCoolFeatureValue = $plan->getFeatureByName('My Cool feature')->value;
```
## Get feature usage

```php
// Get the usage of the feature you can
$myCoolFeatureUsage = SimpleSubscriptionPlanFeature::wherePlanId(1)->whereName('Feature 1')->first()->usage()->first()->used;
```


## Create a Subscription
You can subscribe a user to a plan by using the ```newSubscription()``` function available in the ```HasSubscriptions``` trait. 
First, retrieve an instance of your subscriber model, which typically will be your user model and an instance of the 
plan your user is subscribing to. Once you have retrieved the model instance, you may use the ```newSubscription``` 
method to create the model's subscription.

```php
$user = User::find(1);
$plan = SimpleSubscriptionPlan::whereName('My cool subscription')->first();

$user->newSubscription($plan);
```

you can also specify the start date like this:

```php
$user = User::find(1);
$plan = SimpleSubscriptionPlan::whereName('My cool subscription')->first();

$user->newSubscription($plan, Carbon::today());
```

If no start date is specified, the subscription will start today. The end date is calculated from the settings on the plan.

### Change the Plan

To change the plan do this: 

```php
$newPlan = SimpleSubscriptionPlan::whereName('My second cool plan')->first();
$subscription = SimpleSubscriptionPlanSubscription::whereName('My cool subscription')->first();

// Change subscription plan
$subscription->changePlan($newPlan);
```

If both plans (current and new plan) have the same billing frequency (e.g., `invoice_period` and `invoice_interval`) 
the subscription will retain the same billing dates. If the plans don't have the same billing frequency, 
the subscription will have the new plan billing frequency, starting on the day of the change and 
_the subscription usage data will be cleared_. 
Also, if the new plan has a trial period, and it's a new subscription, the trial period will be applied.

### Subscription Feature Usage

There's multiple ways to determine the usage and ability of a particular feature in the user subscription, the most common one is `canUseFeature`:

The `canUseFeature` method returns `true` or `false` depending on multiple factors:

- Feature _is enabled_.
- Feature value isn't 0
- Or feature has remaining uses available.

```php
$user->subscription('My cool subscription')->canUseFeature('My cool feature');
```

Other feature methods on the user subscription instance are:

- `getFeatureUsage`: returns how many times the user has used a particular feature.
- `getFeatureRemaining`: returns available uses for a particular feature.
- `getFeatureValue`: returns the feature value.

> All methods share the same signature: e.g. `$user->subscription('My cool subscription')->getFeatureUsage('My cool feature');`.

### Record Feature Usage

In order to effectively use the ability methods you will need to keep track of every usage of each feature (or at least those that require it). You may use the `recordFeatureUsage` method available through the user `subscription()` method:

```php
$user->subscription('My cool subscription')->recordFeatureUsage('My cool feature');
```

The `recordFeatureUsage` method accept 3 parameters: the first one is the feature's name, the second one is the quantity of uses to add (default is `1`), and the third one indicates if the addition should be incremental (default behavior), when disabled the usage will be override by the quantity provided. E.g.:

```php
// Increment by 2
$user->subscription('My cool subscription')->recordFeatureUsage('My cool feature', 2);

// Override with 9
$user->subscription('My cool subscription')->recordFeatureUsage('My cool feature', 9, false);
```

### Reduce Feature Usage

Reducing the feature usage is _almost_ the same as incrementing it. Here we only _substract_ a given quantity (default is `1`) to the actual usage:
The difference from the `````recordFeatureUsage````` method is that the ```reduceFeatureUsage``` will not look at reset date
```php
$user->subscription('My cool subscription')->reduceFeatureUsage('My cool feature', 2);
```

### Increase Feature Usage

Increasing the feature usage is _almost_ the same as ```recordFeatureUsage```. this function simply _add_ a given quantity to the value:

One more difference from the ```recordFeatureUsage``` method is that the ```increaseFeatureUsage``` will not look at reset date
```php
$user->subscription('My cool subscription')->increaseFeatureUsage('My cool feature', 2);
```


### Clear The Subscription Usage Data

```php
$user->subscription('My cool subscription')->usage()->delete();
```

### Check Subscription Status

For a subscription to be considered active _one of the following must be `true`_:

- Subscription has an active trial.
- Subscription `ends_at` is in the future.

```php
$user->subscribedToPlanId($planId);
or
$user->subscribedToPlanName('My cool plan');
```

Alternatively you can use the following methods available in the subscription model:

```php
$user->subscription('My cool subscription')->active();
$user->subscription('My cool subscription')->canceled();
$user->subscription('My cool subscription')->ended();
$user->subscription('My cool subscription')->onTrial();
```

> Canceled subscriptions with an active trial or `ends_at` in the future are considered active.

### Renew a Subscription

To renew a subscription you may use the `renew` method available in the subscription model. This will set a new `ends_at` date based on the selected plan and _will clear the usage data_ of the subscription.

```php
$user->subscription('My cool subscription')->renew();
```

_Canceled subscriptions with an ended period can't be renewed._

### Cancel a Subscription

To cancel a subscription, simply use the `cancel` method on the user's subscription:

```php
$user->subscription('My cool subscription')->cancel();
```

By default the subscription will remain active until the end of the period, you may pass `true` to end the subscription _immediately_:

```php
$user->subscription('My cool subscription')->cancel(true);
```
More documentation will be added soon.


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Steen Rabol](https://github.com/rabol)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
