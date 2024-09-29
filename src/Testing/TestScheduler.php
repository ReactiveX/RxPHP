<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
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
        parent::__construct(0, function ($a, $b): int|float {
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

    public function startWithCreate($create): MockObserver
    {
        return $this->startWithTiming($create);
    }

    public function startWithDispose($create, $disposed): MockObserver
    {
        return $this->startWithTiming($create, self::CREATED, self::SUBSCRIBED, $disposed);
    }

    public function startWithTiming($create, int $created = self::CREATED, int $subscribed = self::SUBSCRIBED, int $disposed = self::DISPOSED): ObserverInterface
    {
        $observer     = new MockObserver($this);
        $source       = null;
        $subscription = null;

        $this->scheduleAbsoluteWithState(null, $created, function () use ($create, &$source): \Rx\Disposable\EmptyDisposable {
            $source = $create();

            return new EmptyDisposable();
        });

        $this->scheduleAbsoluteWithState(null, $subscribed, function () use (&$observer, &$source, &$subscription): \Rx\Disposable\EmptyDisposable {
            $subscription = $source->subscribe($observer);

            return new EmptyDisposable();
        });

        $this->scheduleAbsoluteWithState(null, $disposed, function () use (&$subscription): \Rx\Disposable\EmptyDisposable {
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
