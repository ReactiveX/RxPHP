<?php

namespace Rx\Scheduler;

use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\SchedulerInterface;
use InvalidArgumentException;

class ImmediateScheduler implements SchedulerInterface
{
    public function schedule(callable $action, $delay = 0)
    {
        if ($delay !== 0) {
            throw new InvalidArgumentException("ImmediateScheduler does not support a non-zero delay.");
        }

        $action();

        return new EmptyDisposable();
    }

    public function scheduleRecursive(callable $action)
    {

        if (!is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $goAgain    = true;
        $disposable = new CompositeDisposable();

        $recursiveAction = function () use ($action, &$goAgain, $disposable) {
            while ($goAgain) {
                $goAgain = false;
                $disposable->add($this->schedule(function () use ($action, &$goAgain, $disposable) {
                    return $action(function () use (&$goAgain, $action) {
                        $goAgain = true;
                    });
                }));
            }
        };

        $disposable->add($this->schedule($recursiveAction));

        return $disposable;
    }

    /**
     * @inheritDoc
     */
    public function schedulePeriodic(callable $action, $delay, $period)
    {
        throw new \Exception("ImmediateScheduler does not support a non-zero delay.");
    }

    /**
     * Returns milliseconds since the start of the epoch.
     */
    public function now()
    {
        if (function_exists('microtime')) {
            return $milliseconds = floor(microtime(true) * 1000);
        }
        return time() * 1000;
    }
}
