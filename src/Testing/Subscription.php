<?php

declare(strict_types = 1);

namespace Rx\Testing;

class Subscription
{
    private $subscribed;
    private $unsubscribed;

    public function __construct(int $start, int $end = PHP_INT_MAX)
    {
        $this->subscribed   = $start;
        $this->unsubscribed = $end;
    }

    public function equals(Subscription $other)
    {
        return $this->subscribed === $other->subscribed
            && $this->unsubscribed === $other->unsubscribed;
    }

    public function getSubscribed()
    {
        return $this->subscribed;
    }

    public function getUnsubscribed()
    {
        return $this->unsubscribed;
    }

    public function __toString()
    {
        $end = $this->unsubscribed === PHP_INT_MAX ? 'Infinite' : $this->unsubscribed;
        return "Subscription({$this->subscribed}, {$end})";
    }
}
