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
    private int $nextTimer = PHP_INT_MAX;

    private bool $insideInvoke = false;

    private $delayCallback;

    private $currentTimer;

    public function __construct(\Closure|LoopInterface $timerCallableOrLoop)
    {
        $this->delayCallback = $timerCallableOrLoop instanceof LoopInterface ?
            function ($ms, $callable) use ($timerCallableOrLoop): CallbackDisposable {
                $timer = $timerCallableOrLoop->addTimer($ms / 1000, $callable);
                return new CallbackDisposable(function () use ($timer, $timerCallableOrLoop): void {
                    $timerCallableOrLoop->cancelTimer($timer);
                });
            } :
            $timerCallableOrLoop;

        $this->currentTimer = new EmptyDisposable();

        parent::__construct($this->now(), function ($a, $b): int|float {
            return $a - $b;
        });
    }

    private function scheduleStartup(): void
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
            new CallbackDisposable(function () use ($dueTime): void {
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

    public function start(): void
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
