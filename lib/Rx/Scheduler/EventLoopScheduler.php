<?php

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timers;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\SchedulerInterface;
use InvalidArgumentException;

class EventLoopScheduler implements SchedulerInterface
{
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function schedule($action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $timer = $this->loop->addTimer(Timers::MIN_RESOLUTION, $action);

        return new CallbackDisposable(function() use ($timer) { $timer->cancel(); });
    }

    public function scheduleRecursive($action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $group = new CompositeDisposable();
        $scheduler = $this;

        $recursiveAction = null;
        $recursiveAction = function() use ($action, &$scheduler, &$group, &$recursiveAction) {
            $action(
                function() use (&$scheduler, &$group, &$recursiveAction) {
                    $isAdded = false;
                    $isDone  = false;

                    $d = null;
                    $d = $scheduler->schedule(function() use (&$isAdded, &$isDone, &$group, &$recursiveAction, &$d) {
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

    /**
     * @inheritDoc
     */
    public function now()
    {
        return new \DateTime();
    }


}
