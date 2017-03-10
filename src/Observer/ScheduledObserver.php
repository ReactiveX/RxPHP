<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\Disposable\SerialDisposable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

final class ScheduledObserver extends AbstractObserver
{
    /** @var SchedulerInterface */
    private $scheduler;

    /** @var ObserverInterface */
    private $observer;

    /** @var bool */
    private $isAcquired = false;

    /** @var bool */
    private $hasFaulted = false;

    /** @var \Closure[] */
    private $queue = [];

    /** @var SerialDisposable */
    private $disposable;

    /**
     * ScheduledObserver constructor.
     * @param SchedulerInterface $scheduler
     * @param ObserverInterface $observer
     */
    public function __construct(SchedulerInterface $scheduler, ObserverInterface $observer)
    {
        $this->scheduler  = $scheduler;
        $this->observer   = $observer;
        $this->disposable = new SerialDisposable();
    }


    protected function completed()
    {
        $this->queue[] = function () {
            $this->observer->onCompleted();
        };
    }

    protected function next($value)
    {
        $this->queue[] = function () use ($value) {
            $this->observer->onNext($value);
        };
    }

    protected function error(\Throwable $error)
    {
        $this->queue[] = function () use ($error) {
            $this->observer->onError($error);
        };
    }

    public function ensureActive()
    {
        $isOwner = false;
        if (!$this->hasFaulted && count($this->queue) > 0) {
            $isOwner          = !$this->isAcquired;
            $this->isAcquired = true;
        }

        if (!$isOwner) {
            return;
        }

        $this->disposable->setDisposable(
            $this->scheduler->scheduleRecursive(
                function ($recurse) {
                    $parent = $this;
                    if (count($parent->queue) > 0) {
                        $work = array_shift($parent->queue);
                    } else {
                        $parent->isAcquired = false;

                        return;
                    }
                    try {
                        $work();
                    } catch (\Throwable $e) {
                        $parent->queue      = [];
                        $parent->hasFaulted = true;

                        throw $e;
                    }
                    $recurse($parent);
                }
            )
        );
    }

    public function dispose()
    {
        $this->disposable->dispose();
    }
}
