<?php

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;
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
     * @return CallbackDisposable
     */
    public function schedule(callable $action)
    {

        $timer = $this->loop->addTimer(Timer::MIN_INTERVAL, $action);

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
    public function now()
    {
        if (function_exists('microtime')) {
            return microtime(true);
        }
        return time();
    }
}
