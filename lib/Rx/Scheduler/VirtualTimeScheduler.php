<?php

namespace Rx\Scheduler;

use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\SchedulerInterface;

class VirtualTimeScheduler implements SchedulerInterface
{
    protected $clock;
    protected $comparer;
    protected $isEnabled = false;
    protected $queue;

    /**
     * @param integer $initialClock Initial value for the clock.
     * @param callable $comparer Comparer to determine causality of events based on absolute time.
     */
    public function __construct($initialClock = 0, callable $comparer)
    {
        $this->clock    = $initialClock;
        $this->comparer = $comparer;
        $this->queue    = new PriorityQueue();
    }

    public function schedule(callable $action, $delay = 0)
    {

        $invokeAction = function ($scheduler, $action) {
            $action();
            return new EmptyDisposable();
        };

        return $this->scheduleAbsoluteWithState($action, $this->clock + $delay, $invokeAction);
    }

    public function scheduleRecursive(callable $action)
    {
        if (!is_callable($action)) {
            throw new \InvalidArgumentException("Action should be a callable.");
        }

        $goAgain    = true;
        $disposable = new SerialDisposable();

        $recursiveAction = function () use ($action, &$goAgain, $disposable, &$recursiveAction) {
            $disposable->setDisposable($this->schedule(function () use ($action, &$recursiveAction) {
                $action(function () use (&$recursiveAction) {
                    $recursiveAction();
                });
            }));
        };

        $recursiveAction();

        return $disposable;
    }

    public function getClock()
    {
        return $this->clock;
    }

    public function scheduleAbsolute($dueTime, $action)
    {
        $invokeAction = function ($scheduler, $action) {
            $action();
            return new EmptyDisposable();
        };

        return $this->scheduleAbsoluteWithState($action, $dueTime, $invokeAction);
    }

    public function scheduleAbsoluteWithState($state, $dueTime, callable $action)
    {
        $queue = $this->queue;

        $currentScheduler = $this;
        $scheduledItem    = null;
        $run              = function ($scheduler, $state1) use ($action, &$scheduledItem, &$queue) {
            $queue->remove($scheduledItem);

            return $action($scheduler, $state1);
        };

        $scheduledItem = new ScheduledItem($this, $state, $run, $dueTime);

        $this->queue->enqueue($scheduledItem);

        return $scheduledItem->getDisposable();
    }

    public function scheduleRelativeWithState($state, $dueTime, $action)
    {
        $runAt = $this->clock + $dueTime;

        return $this->scheduleAbsoluteWithState($state, $runAt, $action);
    }

    /**
     * @inheritDoc
     */
    public function schedulePeriodic(callable $action, $delay, $period)
    {
        $now = $this->now();

        $nextTime = $now + $delay;

        $disposable = new SerialDisposable();

        $doActionAndReschedule = function () use (&$nextTime, $period, $disposable, $action, &$doActionAndReschedule) {
            $action();
            $nextTime = $nextTime + $period;
            $delay = $nextTime - $this->now();
            if ($delay < 0) {
                $delay = 0;
            }
            $disposable->setDisposable($this->schedule($doActionAndReschedule, $delay));
        };

        $disposable->setDisposable($this->schedule($doActionAndReschedule, $delay));

        return $disposable;
    }

    public function start()
    {
        if (!$this->isEnabled) {

            $this->isEnabled = true;
            $comparer        = $this->comparer;

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
    public function now()
    {
        return $this->clock;
    }
}
