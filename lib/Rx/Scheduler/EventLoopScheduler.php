<?php

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;

final class EventLoopScheduler extends VirtualTimeScheduler
{
    private $nextTimer = PHP_INT_MAX;
    
    private $insideInvoke = false;

    private $delayCallback;

    private $currentTimer;

    /**
     * NewEventLoopScheduler constructor.
     * @param callable|LoopInterface $timerCallableOrLoop
     */
    public function __construct($timerCallableOrLoop)
    {
        // passing a loop directly into the scheduler will be deprecated in the next major release
        $this->delayCallback = $timerCallableOrLoop instanceof LoopInterface ?
            function ($ms, $callable) use ($timerCallableOrLoop) {
                $timer = $timerCallableOrLoop->addTimer($ms / 1000, $callable);
                return new CallbackDisposable(function () use ($timer) {
                    $timer->cancel();
                });
            } :
            $timerCallableOrLoop;

        $this->currentTimer = new EmptyDisposable();

        parent::__construct($this->now(), function ($a, $b) {
            return $a - $b;
        });
    }

    public function scheduleAbsoluteWithState($state, $dueTime, callable $action)
    {
        $disp = parent::scheduleAbsoluteWithState($state, $dueTime, $action);

        if ($this->insideInvoke) {
            return $disp;
        }

        if ($this->nextTimer <= $dueTime) {
            return $disp;
        }

        $this->nextTimer = $this->getClock();

        $this->currentTimer->dispose();
        $this->currentTimer = call_user_func($this->delayCallback, 0, [$this, 'start']);

        return $disp;
    }

    public function start()
    {
        $this->clock = $this->now();

        $this->insideInvoke = true;
        $this->nextTimer    = PHP_INT_MAX;
        while ($this->queue->count() > 0) {
            $next = $this->getNext();
            if ($next !== null) {
                if ($next->getDueTime() > $this->clock) {
                    $this->nextTimer = $next->getDueTime();
                    $this->currentTimer = call_user_func($this->delayCallback, $this->nextTimer - $this->clock, [$this, "start"]);
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
