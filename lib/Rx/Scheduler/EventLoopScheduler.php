<?php

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;

final class EventLoopScheduler extends VirtualTimeScheduler
{
    /** @var callable */
    private $timerCallable;

    private $nextTimer = PHP_INT_MAX;
    
    private $insideInvoke = false;

    /**
     * NewEventLoopScheduler constructor.
     * @param callable|LoopInterface $timerCallableOrLoop
     */
    public function __construct($timerCallableOrLoop)
    {
        // passing a loop directly into the scheduler will be deprecated in the next major release
        $this->timerCallable = $timerCallableOrLoop instanceof LoopInterface ?
            function ($ms, $callable) use ($timerCallableOrLoop) {
                $timerCallableOrLoop->addTimer($ms / 1000, $callable);
            } :
            $timerCallableOrLoop;

        parent::__construct($this->now(), function ($a, $b) {
            return $a - $b;
        });
    }

    public function scheduleAbsoluteWithState($state, $dueTime, callable $action)
    {
        $disp = parent::scheduleAbsoluteWithState($state, $dueTime, $action);
        
        if (!$this->insideInvoke) {
            call_user_func($this->timerCallable, 0, [$this, 'start']);
        }
        
        return $disp;
    }

    public function start()
    {
        $this->clock = $this->now();

        $this->insideInvoke = true;
        while ($this->queue->count() > 0) {
            $next = $this->getNext();
            if ($next !== null) {
                if ($next->getDueTime() > $this->clock) {
                    $this->nextTimer = $next->getDueTime();
                    $timerCallable   = $this->timerCallable;
                    $timerCallable($this->nextTimer - $this->clock, [$this, "start"]);
                    break;
                }

                $next->inVoke();
            }
        }
        $this->insideInvoke = false;
    }

    /**
     * @inheritDoc
     */
    public function now()
    {
        if (function_exists('microtime')) {
            return (int)floor(microtime(true) * 1000);
        }
        return time() * 1000;
    }
}
