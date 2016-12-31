<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class EmptyObservable extends Observable
{

    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $scheduler = $scheduler?: Scheduler::getDefault();

        return $scheduler->schedule(function () use ($observer) {
            $observer->onCompleted();
        });
    }
}
