<?php

declare(strict_types = 1);

namespace Rx\Functional;

use Rx\Notification;
use Rx\Observable;
use Rx\TestCase;
use Rx\MarbleDiagramException;
use Rx\Testing\ColdObservable;
use Rx\Testing\HotObservable;
use Rx\Testing\Recorded;
use Rx\Testing\Subscription;
use Rx\Testing\TestScheduler;

abstract class FunctionalTestCase extends TestCase
{
    /** @var  TestScheduler */
    protected $scheduler;

    const TIME_FACTOR = 10;

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
        $groupTime = -1;

        // calculate subscription time
        $timeOffset = $subscribePoint;
        $subMarker = strpos($marbles, '^');
        if ($subMarker !== false) {
            $timeOffset -= $subMarker * self::TIME_FACTOR;
        }

        for ($i = 0; $i < strlen($marbles); $i++) {
            $now = $groupTime === -1 ? $timeOffset + $i * self::TIME_FACTOR : $groupTime;

            switch ($marbles[$i]) {
                case ' ':
                case '^':
                case '-': // nothing
                    continue 2;
                case '#': // error
                    $events[] = onError($now, $customError ?? new \Exception());
                    continue 2;
                case '|':
                    $events[] = onCompleted($now);
                    continue 2;
                case '(':
                    if ($groupTime !== -1) {
                        throw new MarbleDiagramException('We\'re already inside a group');
                    }
                    $groupTime = $now;
                    continue 2;
                case ')':
                    if ($groupTime === -1) {
                        throw new MarbleDiagramException('We\'re already outside of a group');
                    }
                    $groupTime = -1;
                    continue 2;
                default:
                    $eventKey = $marbles[$i];
                    $events[] = onNext($now, isset($eventMap[$eventKey]) ? $eventMap[$eventKey] : $marbles[$i]);
                    continue 2;
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
            $output .= str_repeat('-', (int)(($time - $lastTime - 1) / self::TIME_FACTOR));

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
        $groupTime = -1;

        for ($i = 0; $i < strlen($marbles); $i++) {
            $now = $groupTime === -1 ? $startTime + $i * self::TIME_FACTOR : $groupTime;

            switch ($marbles[$i]) {
                case ' ':
                case '-':
                    continue 2;
                case '(':
                    if ($groupTime !== -1) {
                        throw new MarbleDiagramException('We\'re already inside a group');
                    }
                    $groupTime = $now;
                    continue 2;
                case ')':
                    if ($groupTime === -1) {
                        throw new MarbleDiagramException('We\'re already outside of a group');
                    }
                    $groupTime = -1;
                    continue 2;
                case '^': // subscribe
                    if ($latestSubscription) {
                        throw new MarbleDiagramException('Trying to subscribe before unsubscribing the previous subscription.');
                    }
                    $latestSubscription = $now;
                    continue 2;
                case '!': // unsubscribe
                    if (!$latestSubscription) {
                        throw new MarbleDiagramException('Trying to unsubscribe before subscribing.');
                    }
                    $events[] = new Subscription($latestSubscription, $now);
                    $latestSubscription = null;
                    break;
                default:
                    throw new MarbleDiagramException('Only "^" and "!" markers are allowed in this diagram.');
            }
        }
        if ($latestSubscription) {
            $events[] = new Subscription($latestSubscription);
        }
        return $events;
    }

    protected function convertMarblesToDisposeTime(string $marbles, $startTime = 0)
    {
        $groupTime = -1;
        $disposeAt = 1000;

        for ($i = 0; $i < strlen($marbles); $i++) {
            $now = $groupTime === -1 ? $startTime + $i * self::TIME_FACTOR : $groupTime++;

            switch ($marbles[$i]) {
                case ' ':
                    continue 2;
                case '!': // unsubscribe
                    $disposeAt = $now;
                    break;
                default:
                    throw new MarbleDiagramException('Only " " and "!" markers are allowed in this diagram.');
            }
        }

        return $disposeAt;
    }

    public function expectObservable(Observable $observable, string $disposeMarble = null): ExpectObservableToBe
    {
        if ($disposeMarble) {
            $disposeAt = $this->convertMarblesToDisposeTime($disposeMarble, 200);

            $results = $this->scheduler->startWithDispose(function () use ($observable) {
                return $observable;
            }, $disposeAt);
        } else {
            $results = $this->scheduler->startWithCreate(function () use ($observable) {
                return $observable;
            });
        }

        $messages = $results->getMessages();

        return new class($messages) extends FunctionalTestCase implements ExpectObservableToBe
        {
            private $messages;

            public function __construct(array $messages)
            {
                parent::__construct();
                $this->messages = $messages;
            }

            public function toBe(string $expected, array $values = [], string $errorMessage = null)
            {
                $error = $errorMessage ? new \Exception($errorMessage) : null;

                $this->assertEquals(
                    $this->convertMarblesToMessages($expected, $values, $error, 200),
                    $this->messages
                );
            }
        };
    }

    public function expectSubscriptions(array $subscriptions): ExpectSubscriptionsToBe
    {
        return new class($subscriptions) extends FunctionalTestCase implements ExpectSubscriptionsToBe
        {
            private $subscriptions;

            public function __construct(array $subscriptions)
            {
                parent::__construct();
                $this->subscriptions = $subscriptions;
            }

            public function toBe(string $subscriptionsMarbles)
            {
                $this->assertEquals(
                    $this->convertMarblesToSubscriptions($subscriptionsMarbles, 200),
                    $this->subscriptions
                );
            }
        };
    }
}

interface ExpectSubscriptionsToBe
{
    public function toBe(string $subscriptionsMarbles);
}

interface ExpectObservableToBe
{
    public function toBe(string $expected, array $values = [], string $errorMessage = null);
}
