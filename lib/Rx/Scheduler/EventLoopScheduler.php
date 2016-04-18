<?php

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\SchedulerInterface;

class EventLoopScheduler implements SchedulerInterface
{
    private $loop;

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
        $delay = $delay / 1000; // switch from ms to seconds for react
        $timer = $this->loop->addTimer($delay, $action);

        return new CallbackDisposable(function () use ($timer) {
            $timer->cancel();
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
