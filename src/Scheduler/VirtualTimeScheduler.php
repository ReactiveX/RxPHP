<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use Rx\AsyncSchedulerInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;

class VirtualTimeScheduler implements AsyncSchedulerInterface
{
    /**
     * @var int
     */
    protected $clock;

    /**
     * @var callable
     */
    protected $comparer;

    /**
     * @var bool
     */
    protected $isEnabled = false;

    /**
     * @var PriorityQueue
     */
    protected $queue;

    /**
     * @param integer $initialClock Initial value for the clock.
     * @param callable $comparer Comparer to determine causality of events based on absolute time.
     */
    public function __construct(int $initialClock, callable $comparer)
    {
        $this->clock    = $initialClock;
        $this->comparer = $comparer;
        $this->queue    = new PriorityQueue();
    }

    public function schedule(callable $action, $delay = 0): DisposableInterface
    {

        $invokeAction = function ($scheduler, $action) {
            $action();
            return new EmptyDisposable();
        };

        return $this->scheduleAbsoluteWithState($action, $this->now() + $delay, $invokeAction);
    }

    public function scheduleRecursive(callable $action): DisposableInterface
    {
        $disposable = new SerialDisposable();

        $recursiveAction = function () use ($action, $disposable, &$recursiveAction) {
            $disposable->setDisposable($this->schedule(function () use ($action, &$recursiveAction) {
                $action(function () use (&$recursiveAction) {
                    $recursiveAction();
                });
            }));
        };

        $recursiveAction();

        return $disposable;
    }

    public function getClock(): int
    {
        return $this->clock;
    }

    public function scheduleAbsolute(int $dueTime, callable $action): DisposableInterface
    {
        $invokeAction = function ($scheduler, $action) {
            $action();
            return new EmptyDisposable();
        };

        return $this->scheduleAbsoluteWithState($action, $dueTime, $invokeAction);
    }

    /**
     * @param mixed $state
     */
    public function scheduleAbsoluteWithState($state, int $dueTime, callable $action): DisposableInterface
    {
        $queue = $this->queue;

        $scheduledItem = null;

        $run = function ($scheduler, $state1) use ($action, &$scheduledItem, &$queue) {
            assert($scheduledItem instanceof ScheduledItem); /** @phpstan-ignore-line */
            $queue->remove($scheduledItem);

            return $action($scheduler, $state1);
        };

        $scheduledItem = new ScheduledItem($this, $state, $run, $dueTime);

        $this->queue->enqueue($scheduledItem);

        return new CallbackDisposable(function () use ($scheduledItem) {
            $scheduledItem->getDisposable()->dispose();
            $this->queue->remove($scheduledItem);
        });
    }

    /**
     * @param mixed $state
     */
    public function scheduleRelativeWithState($state, int $dueTime, callable $action): DisposableInterface
    {
        $runAt = $this->now() + $dueTime;

        return $this->scheduleAbsoluteWithState($state, $runAt, $action);
    }

    /**
     * @inheritDoc
     */
    public function schedulePeriodic(callable $action, $delay, $period): DisposableInterface
    {
        $now = $this->now();

        $nextTime = $now + $delay;

        $disposable = new SerialDisposable();

        $doActionAndReschedule = function () use (&$nextTime, $period, $disposable, $action, &$doActionAndReschedule) {
            $action();
            $nextTime += $period;
            $delay = $nextTime - $this->now();
            if ($delay < 0) {
                $delay = 0;
            }
            $disposable->setDisposable($this->schedule($doActionAndReschedule, $delay));
        };

        $disposable->setDisposable($this->schedule($doActionAndReschedule, $delay));

        return $disposable;
    }

    /**
     * @return void
     */
    public function start()
    {
        if (!$this->isEnabled) {

            $this->isEnabled = true;

            $comparer = $this->comparer;

            do {
                $next = $this->getNext();

                if ($next !== null) {
                    if ($comparer($next->getDueTime(), $this->clock) > 0) {
                        $this->clock = $next->getDueTime();
                    }

                    $next->inVoke();
                } else {
                    $this->isEnabled = false;
                }

            } while ($this->isEnabled);
        }
    }

    /**
     * @return ?ScheduledItem
     */
    public function getNext()
    {
        while ($this->queue->count() > 0) {
            $next = $this->queue->peek();
            if ($next->isCancelled()) {
                $this->queue->dequeue();
            } else {
                return $next;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function now(): int
    {
        return $this->clock;
    }
}
