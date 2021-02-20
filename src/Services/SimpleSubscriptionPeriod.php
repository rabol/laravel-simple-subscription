<?php

declare(strict_types=1);

namespace Rabol\SimpleSubscription\Services;

use Carbon\Carbon;

class SimpleSubscriptionPeriod
{
    protected Carbon $start;
    protected Carbon $end;
    protected string $interval;
    protected int $intervalCount = 1;

    public function __construct(?string $interval = 'month', ?int $intervalCount = 1, ?Carbon $start = now())
    {
        $this->interval = $interval;
        $this->start = $start;
        $this->intervalCount = $intervalCount;

        switch ($this->interval) 
        {
            case 'day': $this->end->addDays($this->intervalCount);  break;
            case 'week': $this->end->addWeeks($this->intervalCount); break;
            case 'month': $this->end->addMonths($this->intervalCount); break;
            case 'year': $this->end->addYears($this->intervalCount); break;            
            default:
                 break;
        }
    }

    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    public function getEndDate(): Carbon
    {
        return $this->end;
    }

    public function getInterval(): string
    {
        return $this->interval;
    }

    public function getIntervalCount(): int
    {
        return $this->intervalCount;
    }
}
