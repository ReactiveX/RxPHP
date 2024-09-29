<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\AsyncSchedulerInterface;

class TimerObservable extends Observable
{

    public function __construct(
        private readonly int            $dueTime,
        private readonly AsyncSchedulerInterface $scheduler
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->scheduler->schedule(
            function () use ($observer): void {
                $observer->onNext(0);
                $observer->onCompleted();
            },
            $this->dueTime
        );
    }
}
