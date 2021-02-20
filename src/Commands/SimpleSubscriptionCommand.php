<?php

namespace Rabol\SimpleSubscription\Commands;

use Illuminate\Console\Command;

class SimpleSubscriptionCommand extends Command
{
    public $signature = 'laravel-simple-subscription';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
