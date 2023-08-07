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
    public function startWithCreate($create): MockObserver
    {
        return $this->startWithTiming($create);
    }

    /**
     * @template T
     * @param (callable(): ObservableInterface<T>) $create
     * @return MockObserver<T>
     */
    public function startWithDispose($create, $disposed): MockObserver
    {
        return $this->startWithTiming($create, self::CREATED, self::SUBSCRIBED, $disposed);
    }

    /**
     * @template T
     * @param (callable(): ObservableInterface<T>) $create
     * @return MockObserver<T>
     */
    public function startWithTiming($create, $created = self::CREATED, $subscribed = self::SUBSCRIBED, $disposed = self::DISPOSED): ObserverInterface
    {
        $observer     = new MockObserver($this);
        $source       = null;
        $subscription = null;

        $this->scheduleAbsoluteWithState(null, $created, function () use ($create, &$source) {
            $source = $create();

            return new EmptyDisposable();
        });

        $this->scheduleAbsoluteWithState(null, $subscribed, function () use (&$observer, &$source, &$subscription) {
            $subscription = $source->subscribe($observer);

            return new EmptyDisposable();
        });

        $this->scheduleAbsoluteWithState(null, $disposed, function () use (&$subscription) {
            $subscription->dispose();

            return new EmptyDisposable();
        });
        $this->start();

        return $observer;
    }

    public function createObserver(): MockObserver
    {
        return new MockObserver($this);
    }
}
