<?php

namespace Rabol\SimpleSubscription;

use Illuminate\Support\Facades\Validator;
use Rabol\SimpleSubscription\Commands\SimpleSubscriptionCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SimpleSubscriptionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
/*
        $package
            ->name('laravel-simple-subscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations(
                [
                    'create_simple_subscription_plans_table',
                    'create_simple_subscription_plan_subscription_usages_table',
                    'create_simple_subscription_plan_features_table',
                    'create_simple_subscription_plan_subscriptions_table',
                ]
            )
            ->hasCommand(SimpleSubscriptionCommand::class);
*/
        $package
            ->name('laravel-simple-subscription')
             ->hasMigrations(
                [
                    'create_simple_subscription_plans_table',
                    'create_simple_subscription_plan_subscription_usages_table',
                    'create_simple_subscription_plan_features_table',
                    'create_simple_subscription_plan_subscriptions_table'
                ]
                );
 

    }

    public function boot()
    {
        parent::boot();

        // Add strip_tags validation rule
        Validator::extend('strip_tags', function ($attribute, $value) {
            if ($attribute == 'name') {
                return strip_tags($value) === $value;
            }

            return strip_tags($value) === $value;
        }, "We don't allow HTML tags in the input.");

        return $this;
    }
}
