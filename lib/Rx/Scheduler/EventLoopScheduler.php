<?php

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;

final class EventLoopScheduler extends VirtualTimeScheduler
{
    /** @var callable */
    private $timerCallable;

    private $nextTimer = PHP_INT_MAX;

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

        call_user_func($this->timerCallable, 0, [$this, 'start']);
    }

    public function start()
    {
        $this->clock = $this->now();

        while ($this->queue->count() > 0) {
            if ($this->queue->peek()->getDueTime() > $this->clock) {
                $this->nextTimer = $this->queue->peek()->getDueTime();
                $timerCallable = $this->timerCallable;
                $timerCallable($this->nextTimer - $this->clock, [$this, "start"]);
                break;
            }

            $next = $this->getNext();
            if ($next !== null) {
                $next->inVoke();
            }
        }
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
