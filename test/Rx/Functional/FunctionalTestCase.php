<?php

namespace Rx\Functional;

use Rx\Notification;
use Rx\TestCase;
use Rx\MarbleDiagramError;
use Rx\Testing\ColdObservable;
use Rx\Testing\HotObservable;
use Rx\Testing\Recorded;
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

    /**
     * @param Recorded[] $expected
     * @param Recorded[] $recorded
     */
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

    /**
     * @param Recorded[] $expected
     * @param Recorded[] $recorded
     */
    public function assertMessagesNotEqual(array $expected, array $recorded)
    {
        if (count($expected) !== count($recorded)) {
            $this->assertTrue(true);
            return;
        }

        for ($i = 0, $count = count($expected); $i < $count; $i++) {
            if (!$expected[$i]->equals($recorded[$i])) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail('Expected messages do match the actual');
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

    protected function createCold(string $events, array $eventMap = [], \Exception $customError = null)
    {
        return new ColdObservable($this->scheduler, $this->convertMarblesToMessages($events, $eventMap, $customError));
    }

    protected function createHot(string $events, array $eventMap = [], \Exception $customError = null)
    {
        return new HotObservable($this->scheduler, $this->convertMarblesToMessages($events, $eventMap, $customError, 200));
    }

    protected function createHotObservable(array $events)
    {
        return new HotObservable($this->scheduler, $events);
    }

    protected function createTestScheduler()
    {
        return new TestScheduler();
    }

    protected function convertMarblesToMessages(string $marbles, array $eventMap = [], \Exception $customError = null, $subscribePoint = 0)
    {
        /** @var Recorded $events */
        $events = [];
        $zero = 0;

        for ($i = 0; $i < strlen($marbles); $i++) {
            switch ($marbles[$i]) {
                case ' ':
                case '-': // nothing
                    continue;
                case '#': // error
                    $events[] = onError($i * 10, $customError ?? new \Exception());
                    continue;
                case '^': // this is the subscribe point
                    $zero = $i * 10;
                    continue;
                case '|': //
                    $events[] = onCompleted($i * 10);
                    continue;
                default:
                    $eventKey = $marbles[$i];
                    $events[] = onNext($i * 10, isset($eventMap[$eventKey]) ? $eventMap[$eventKey] : $marbles[$i]);
                    continue;
            }
        }

        if ($subscribePoint != 0) { // zero is cold
            $oldEvents = $events;
            $events = [];
            /** @var Recorded $event */
            foreach ($oldEvents as $event) {
                $events[] = new Recorded($event->getTime() + $subscribePoint - $zero, $event->getValue());
            }
        }

        return $events;
    }

    protected function convertMessagesToMarbles($messages)
    {
        $output = '';
        $lastTime = 199;

        /** @var Recorded $message */
        foreach ($messages as $message) {
            $time = $message->getTime();
            /** @var Notification $value */
            $value = $message->getValue();
            $output .= str_repeat('-', floor(($time - $lastTime - 1) / 10));

            $lastTime = $time;

            $value->accept(
                function ($x) use (&$output) {
                    $output .= $x;
                },
                function (\Exception $e) use (&$output) {
                    $output .= '#';
                },
                function () use (&$output) {
                    $output .= '|';
                }
            );
        }

        return $output;
    }

    protected function convertMarblesToSubscriptions(string $marbles, $startTime = 0)
    {
        $latestSubscription = null;
        $events = [];

        for ($i = 0; $i < strlen($marbles); $i++) {
            switch ($marbles[$i]) {
                case ' ':
                case '-':
                    break;
                case '^': // subscribe
                    if ($latestSubscription) {
                        throw new MarbleDiagramError('Trying to subscribe before unsubscribing the previous subscription.');
                    }
                    $latestSubscription = $startTime + $i * 10;
                    continue;
                case '!': // unsubscribe
                    if (!$latestSubscription) {
                        throw new MarbleDiagramError('Trying to unsubscribe before subscribing.');
                    }
                    $events[] = new Subscription($latestSubscription, $startTime + $i * 10);
                    $latestSubscription = null;
                    break;
                default:
                    throw new MarbleDiagramError('Only "^" and "!" markers are allowed in this diagram.');
                    continue;
            }
        }
        if ($latestSubscription) {
            $events[] = new Subscription($latestSubscription);
        }
        return $events;
    }
}
