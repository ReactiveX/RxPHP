<?php

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class EmptyObservable extends Observable
{
    private $scheduler;

    public function __construct(SchedulerInterface $scheduler = null)
    {
        $this->scheduler = $scheduler ?: Scheduler::getDefault();
    }

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->scheduler->schedule(function () use ($observer) {
            $observer->onCompleted();
        });
    }
}
