<?php

declare(strict_types = 1);

namespace Rx\Testing;

class Subscription
{
    /**
     * @var int
     */
    private $subscribed;

    /**
     * @var int
     */
    private $unsubscribed;

    public function __construct(int $start, int $end = PHP_INT_MAX)
    {
        $this->subscribed   = $start;
        $this->unsubscribed = $end;
    }

    /**
     * @param Subscription $other
     * @return bool
     */
    public function equals(Subscription $other): bool
    {
        return $this->subscribed === $other->subscribed
            && $this->unsubscribed === $other->unsubscribed;
    }

    /**
     * @return int
     */
    public function getSubscribed(): int
    {
        return $this->subscribed;
    }

    /**
     * @return int
     */
    public function getUnsubscribed(): int
    {
        return $this->unsubscribed;
    }

    public function __toString()
    {
        $end = $this->unsubscribed === PHP_INT_MAX ? 'Infinite' : $this->unsubscribed;
        return "Subscription({$this->subscribed}, {$end})";
    }
}
