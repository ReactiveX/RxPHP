<?php

namespace Rx\Testing;

class Subscription
{
    private $subscribed;
    private $unsubscribed;

    public function __construct($start, $end = PHP_INT_MAX)
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
        return 'Subscription('
        . $this->subscribed
        . ', ' . ($this->unsubscribed === PHP_INT_MAX ? 'Inifinite' : $this->unsubscribed)
        . ')';
    }
}
