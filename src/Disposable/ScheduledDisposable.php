<?php

declare(strict_types = 1);

namespace Rx\Disposable;

use Rx\DisposableInterface;
use Rx\SchedulerInterface;

class ScheduledDisposable implements DisposableInterface
{
    public function __construct(
        private SchedulerInterface   $scheduler,
        private  DisposableInterface $disposable,
        protected bool               $isDisposed = false
    ) {
    }

    public function dispose(): void
    {
        if ($this->isDisposed) {
            return;
        }

        $this->isDisposed = true;

        $this->scheduler->schedule(function (): void {
            $this->disposable->dispose();
        });
    }
}
