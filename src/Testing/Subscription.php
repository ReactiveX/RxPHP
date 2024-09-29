<?php

declare(strict_types = 1);

namespace Rx\Testing;

class Subscription
{
    private int $subscribed;
    private int $unsubscribed;

    public function __construct(int $start, int $end = PHP_INT_MAX)
    {
        $this->subscribed   = $start;
        $this->unsubscribed = $end;
    }

    public function equals(Subscription $other): bool
    {
        return $this->subscribed === $other->subscribed
            && $this->unsubscribed === $other->unsubscribed;
    }

    public function getSubscribed(): int
    {
        return $this->subscribed;
    }

    public function getUnsubscribed(): int
    {
        return $this->unsubscribed;
    }

    public function __toString(): string
    {
        $end = $this->unsubscribed === PHP_INT_MAX ? 'Infinite' : $this->unsubscribed;
        return "Subscription({$this->subscribed}, {$end})";
    }
}
