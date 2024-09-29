<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ErrorObservable extends Observable
{

    public function __construct(
        private readonly \Throwable $error,
        private readonly SchedulerInterface $scheduler
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->scheduler->schedule(function () use ($observer): void {
            $observer->onError($this->error);
        });
    }
}
