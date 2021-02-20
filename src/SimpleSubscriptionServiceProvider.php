<?php

namespace Rabol\SimpleSubscription;

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
        $package
            ->name('laravel-simple-subscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations(
                'create_simple_subscription_plans_table',
                'create_simple_subscription_plan_subscription_usages_table',
                'create_simple_subscription_plan_features_table',
                'create_simple_subscription_plan_subscriptions_table'
            )
            ->hasCommand(SimpleSubscriptionCommand::class);
    }
}
