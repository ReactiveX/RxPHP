<?php

namespace Rx\Scheduler;

use Interop\Async\Loop;

final class EventLoopScheduler extends VirtualTimeScheduler
{
    private $nextTimer = PHP_INT_MAX;

    private $insideInvoke = false;

    public function __construct()
    {
        parent::__construct($this->now(), function ($a, $b) {
            return $a - $b;
        });
    }

    public function scheduleAbsoluteWithState($state, $dueTime, callable $action)
    {
        $disp = parent::scheduleAbsoluteWithState($state, $dueTime, $action);

        if (!$this->insideInvoke) {
            Loop::delay(0, [$this, 'start']);
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
                    Loop::delay($this->nextTimer - $this->clock, [$this, "start"]);
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
