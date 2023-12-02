<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\Scheduler\VirtualTimeScheduler;

class TestScheduler extends VirtualTimeScheduler
{
    const CREATED = 100;
    const SUBSCRIBED = 200;
    const DISPOSED = 1000;

    public function __construct()
    {
        parent::__construct(0, function ($a, $b) {
            return $a - $b;
        });
    }

    /**
     * @param mixed $state
     */
    public function scheduleAbsoluteWithState($state, int $dueTime, callable $action): DisposableInterface
    {
        if ($dueTime <= $this->clock) {
            $dueTime = $this->clock + 1;
        }

        return parent::scheduleAbsoluteWithState($state, $dueTime, $action);
    }

    /**
     * @template T
     * @param (callable(): ObservableInterface<T>) $create
     * @return MockObserver<T>
     */
    public function startWithCreate(callable $create): MockObserver
    {
        return $this->startWithTiming($create);
    }

    /**
     * @template T
     * @param (callable(): ObservableInterface<T>) $create
     * @return MockObserver<T>
     */
    public function startWithDispose(callable $create, int $disposed): MockObserver
    {
        return $this->startWithTiming($create, self::CREATED, self::SUBSCRIBED, $disposed);
    }

    /**
     * @template T
     * @param (callable(): ObservableInterface<T>) $create
     * @return MockObserver<T>
     */
    public function startWithTiming(callable $create, int $created = self::CREATED, int $subscribed = self::SUBSCRIBED, int $disposed = self::DISPOSED): MockObserver
    {
        $observer     = new MockObserver($this);
        $source       = null;
        $subscription = null;

        $this->scheduleAbsoluteWithState(null, $created, function () use ($create, &$source) {
            $source = $create();

            return new EmptyDisposable();
        });

        $this->scheduleAbsoluteWithState(null, $subscribed, function () use (&$observer, &$source, &$subscription) {
            assert($source instanceof ObservableInterface);
            $subscription = $source->subscribe($observer);

            return new EmptyDisposable();
        });

        $this->scheduleAbsoluteWithState(null, $disposed, function () use (&$subscription) {
            assert($subscription instanceof DisposableInterface);
            $subscription->dispose();

            return new EmptyDisposable();
        });
        $this->start();

        return $observer;
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function createObserver(): MockObserver
    {
        return new MockObserver($this);
    }
}
