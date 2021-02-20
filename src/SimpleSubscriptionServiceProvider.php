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
            ->hasMigration('create_laravel_simple_subscription_table')
            ->hasCommand(SimpleSubscriptionCommand::class);
    }
}
