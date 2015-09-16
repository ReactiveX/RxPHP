<?php

namespace Rx\Functional;

use PHPUnit_Framework_ExpectationFailedException;
use Rx\Scheduler\VirtualTimeScheduler;
use Rx\TestCase;
use Rx\Testing\ColdObservable;
use Rx\Testing\HotObservable;
use Rx\Testing\Subscription;
use Rx\Testing\TestScheduler;

abstract class FunctionalTestCase extends TestCase
{
    /** @var  TestScheduler */
    protected $scheduler;

    public function setup()
    {
        $this->scheduler = $this->createTestScheduler();
    }

    public function assertMessages(array $expected, array $recorded)
    {
        if (count($expected) !== count($recorded)) {
            $this->fail(sprintf('Expected message count %d does not match actual count %d.', count($expected), count($recorded)));
        }

        for ($i = 0, $count = count($expected); $i < $count; $i++) {
            if (!$expected[$i]->equals($recorded[$i])) {
                $this->fail($expected[$i] . ' does not equal ' . $recorded[$i]);
            }
        }

        $this->assertTrue(true); // success
    }

    public function assertSubscription(HotObservable $observable, Subscription $expected)
    {
        $subscriptionCount = count($observable->getSubscriptions());

        if ($subscriptionCount === 0) {
            $this->fail('Observable has no subscriptions.');
        }

        if ($subscriptionCount > 1) {
            $this->fail('Observable has more than 1 subscription.');
        }

        list($actual) = $observable->getSubscriptions();

        if (!$expected->equals($actual)) {
            $this->fail(sprintf("Expected subscription '%s' does not match actual subscription '%s'", $expected, $actual));
        }

        $this->assertTrue(true); // success
    }

    public function assertSubscriptions(array $expected, array $recorded)
    {
        if (count($expected) !== count($recorded)) {
            $this->fail(sprintf('Expected subscription count %d does not match actual count %d.', count($expected), count($recorded)));
        }

        for ($i = 0, $count = count($expected); $i < $count; $i++) {
            if (!$expected[$i]->equals($recorded[$i])) {
                $this->fail($expected[$i] . ' does not equal ' . $recorded[$i]);
            }
        }

        $this->assertTrue(true); // success
    }

    /**
     * @param callable $callback
     */
    protected function assertException(callable $callback)
    {
        try {
            $callback();
        } catch (\Exception $e) {
            return;
        }
        $this->fail('Expected the callback to throw an exception.');
    }


    protected function createColdObservable(array $events)
    {
        return new ColdObservable($this->scheduler, $events);
    }

    protected function createHotObservable(array $events)
    {
        return new HotObservable($this->scheduler, $events);
    }

    protected function createTestScheduler()
    {
        return new TestScheduler();
    }
}
