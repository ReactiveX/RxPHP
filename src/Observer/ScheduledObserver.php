<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\Disposable\SerialDisposable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

final class ScheduledObserver extends AbstractObserver
{
    private SerialDisposable $disposable;

    /**
     * ScheduledObserver constructor.
     */
    public function __construct(
        private SchedulerInterface $scheduler,
        private ObserverInterface $observer,
        private bool $isAcquired = false,
        private bool $hasFaulted = false,
        /** @var \Closure[] */
        private array $queue = []
    ) {
        $this->disposable = new SerialDisposable();
    }


    protected function completed(): void
    {
        $this->queue[] = function (): void {
            $this->observer->onCompleted();
        };
    }

    protected function next($value): void
    {
        $this->queue[] = function () use ($value): void {
            $this->observer->onNext($value);
        };
    }

    protected function error(\Throwable $error): void
    {
        $this->queue[] = function () use ($error): void {
            $this->observer->onError($error);
        };
    }

    public function ensureActive(): void
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
                function ($recurse): void {
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

    public function dispose(): void
    {
        $this->disposable->dispose();
    }
}
