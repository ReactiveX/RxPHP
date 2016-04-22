<?php

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\SchedulerInterface;

class EventLoopScheduler implements SchedulerInterface
{
    private $loop;

    private $insideScheduledAction = false;

    private $actionForTimes = [];

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param callable $action
     * @param $delay
     * @return CallbackDisposable
     */
    public function schedule(callable $action, $delay = 0)
    {
        $canceled = false;

        $outerAction = function () use ($action, &$canceled) {
            if ($canceled) {
                return;
            }
            $this->insideScheduledAction = true;
            $action();
            foreach ($this->actionForTimes as $delay => $actionForTime) {
                $timer = $this->loop->addTimer($delay, function () use ($actionForTime) {
                    foreach ($actionForTime as $action) {
                        $action();
                    }
                });
            }
            $this->actionForTimes = [];
            $this->insideScheduledAction = false;
        };

        $delay = (string)$delay / 1000; // switch from ms to seconds for react

        if (!$this->insideScheduledAction) {
            if ($delay === 0) {
                $this->loop->nextTick($outerAction);

                return new CallbackDisposable(function () use (&$canceled) {
                    $canceled = true;
                });
            }
            $timer = $this->loop->addTimer($delay, function () use ($outerAction) {
                $outerAction();
            });

            return new CallbackDisposable(function () use ($timer) {
                $timer->cancel();
            });
        }

        if (!isset($this->actionForTimes[$delay])) {
            $this->actionForTimes[$delay] = [];
        }

        $this->actionForTimes[$delay][] = $outerAction;

        return new CallbackDisposable(function () use (&$canceled) {
            $canceled = true;
        });
    }

    public function scheduleRecursive(callable $action)
    {
        $group = new CompositeDisposable();

        $recursiveAction = null;

        $recursiveAction = function () use ($action, &$group, &$recursiveAction) {
            $action(
                function () use (&$group, &$recursiveAction) {
                    $isAdded = false;
                    $isDone  = false;

                    $d = null;
                    $d = $this->schedule(function () use (&$isAdded, &$isDone, &$group, &$recursiveAction, &$d) {
                        if (is_callable($recursiveAction)) {
                            $recursiveAction();
                        } else {
                            throw new \Exception("recursiveAction is not callable");
                        }

                        if ($isAdded) {
                            $group->remove($d);
                        } else {
                            $isDone = true;
                        }
                    });

                    if (!$isDone) {
                        $group->add($d);
                        $isAdded = true;
                    }
                }
            );
        };

        $group->add($this->schedule($recursiveAction));

        return $group;
    }

    /**
     * @inheritDoc
     */
    public function schedulePeriodic(callable $action, $delay, $period)
    {
        $delay = $delay / 1000;
        $period = $period / 1000;

        $disposed = false;

        $timer = $this->loop->addTimer($delay, function () use ($action, $period, &$timer, &$disposed) {
            $action();
            if (!$disposed) {
                $timer = $this->loop->addPeriodicTimer($period, function () use ($action) {
                    $action();
                });
            }
        });

        return new CallbackDisposable(function () use (&$timer, &$disposed) {
            $disposed = true;
            $timer->cancel();
        });
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
