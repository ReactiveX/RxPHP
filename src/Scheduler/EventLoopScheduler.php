<?php

declare(strict_types = 1);

namespace Rx\Scheduler;

use React\EventLoop\LoopInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;

final class EventLoopScheduler extends VirtualTimeScheduler
{
    private $nextTimer = PHP_INT_MAX;

    private $insideInvoke = false;

    private $delayCallback;

    private $currentTimer;

    /**
     * EventLoopScheduler constructor.
     * @param callable|LoopInterface $timerCallableOrLoop
     */
    public function __construct($timerCallableOrLoop)
    {
        $this->delayCallback = $timerCallableOrLoop instanceof LoopInterface ?
            function ($ms, $callable) use ($timerCallableOrLoop) {
                $timer = $timerCallableOrLoop->addTimer($ms / 1000, $callable);
                return new CallbackDisposable(function () use ($timer, $timerCallableOrLoop) {
                    $timerCallableOrLoop->cancelTimer($timer);
                });
            } :
            $timerCallableOrLoop;

        $this->currentTimer = new EmptyDisposable();

        parent::__construct($this->now(), function ($a, $b) {
            return $a - $b;
        });
    }

    private function scheduleStartup()
    {
        if ($this->insideInvoke) {
            return;
        }
        $this->currentTimer->dispose();
        $this->nextTimer    = $this->getClock();
        $this->currentTimer = call_user_func($this->delayCallback, 0, [$this, 'start']);
    }

    public function scheduleAbsoluteWithState($state, int $dueTime, callable $action): DisposableInterface
    {
        $disp = new CompositeDisposable([
            parent::scheduleAbsoluteWithState($state, $dueTime, $action),
            new CallbackDisposable(function () use ($dueTime) {
                if ($dueTime > $this->nextTimer) {
                    return;
                }
                $this->scheduleStartup();
            })
        ]);

        if ($this->insideInvoke) {
            return $disp;
        }

        if ($this->nextTimer <= $dueTime) {
            return $disp;
        }

        $this->scheduleStartup();

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
    public function now(): int
    {
        return (int)floor(microtime(true) * 1000);
    }
}
