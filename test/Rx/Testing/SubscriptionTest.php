<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\TestCase;

class SubscriptionTest extends TestCase
{
    public function testSubscriptionGetters()
    {
        $sub = new Subscription(1, 2);

        $this->assertEquals(1, $sub->getSubscribed());
        $this->assertEquals(2, $sub->getUnsubscribed());
    }

    public function testSubscriptionToString()
    {
        $this->assertEquals(new Subscription(1, 2), "Subscription(1, 2)");
    }
}
