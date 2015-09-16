<?php

namespace Rx\Scheduler;

use InvalidArgumentException;
use RuntimeException;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\SchedulerInterface;
use SplPriorityQueue;

class VirtualTimeScheduler implements SchedulerInterface
{
    protected $clock;
    protected $comparer;
    protected $isEnabled = false;
    protected $queue;

    /**
     * @param integer  $initialClock Initial value for the clock.
     * @param callable $comparer     Comparer to determine causality of events based on absolute time.
     */
    public function __construct($initialClock = 0, $comparer)
    {
        if (! is_callable($comparer)) {
            throw new RuntimeException('Comparer should be a callable.');
        }

        $this->clock    = $initialClock;
        $this->comparer = $comparer;
        $this->queue    = new PriorityQueue();
    }

    public function schedule($action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $invokeAction = function($scheduler, $action) {
            $action();
            return new EmptyDisposable();
        };

        return $this->scheduleAbsoluteWithState($action, $this->clock, $invokeAction);
    }

    public function scheduleRecursive($action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $group = new CompositeDisposable();

        $recursiveAction = function() use ($action, $group, &$recursiveAction) {
            $action(
                function() use ($group, &$recursiveAction) {
                    $isAdded = false;
                    $isDone  = true;

                    $d = $this->schedule(function() use (&$isAdded, &$isDone, $group, &$recursiveAction, &$d) {
                        if (!is_callable($recursiveAction)) {
                            throw new \Exception("recursiveAction is not callable");
                        }

                        $recursiveAction();

                        if ($isAdded) {
                            $group->remove($d);
                        } else {
                            $isDone = true;
                        }
                    });

                    if ( ! $isDone) {
                        $group->add($d);
                        $isAdded = true;
                    }
                }
            );
        };

        $group->add($this->schedule($recursiveAction));

        return $group;
    }

    public function getClock()
    {
        return $this->clock;
    }

    public function scheduleAbsolute($dueTime, $action)
    {
        $invokeAction = function($scheduler, $action) {
            $action();
            return new EmptyDisposable();
        };

        return $this->scheduleAbsoluteWithState($action, $dueTime, $invokeAction);
    }

    public function scheduleAbsoluteWithState($state, $dueTime, $action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $queue = $this->queue;

        $currentScheduler = $this;
        $scheduledItem    = null;
        $run              = function($scheduler, $state1) use ($action, &$scheduledItem, &$queue) {
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

    public function start()
    {
        if ( ! $this->isEnabled) {

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
