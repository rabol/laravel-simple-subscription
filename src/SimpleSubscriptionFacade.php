<?php

namespace Rabol\SimpleSubscription;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Rabol\SimpleSubscription\SimpleSubscription
 */
class SimpleSubscriptionFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SimpleSubscription::class;
    }
}
