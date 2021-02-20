# Simple subscription package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rabol/laravel-simple-subscription.svg?style=flat-square)](https://packagist.org/packages/rabol/laravel-simple-subscription)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/rabol/laravel-simple-subscription/run-tests?label=tests)](https://github.com/rabol/laravel-simple-subscription/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/rabol/laravel-simple-subscription/Check%20&%20fix%20styling?label=code%20style)](https://github.com/rabol/laravel-simple-subscription/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/rabol/laravel-simple-subscription.svg?style=flat-square)](https://packagist.org/packages/rabol/laravel-simple-subscription)


This is a simple to use subscription package for Laravel.

It is heavvly inspired by the renvex/laravel-subscriptions package, just simpler and working :)

Sorry, My point is that the renvex packages seems to be abandond.

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

```php
Comming soon
```

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
