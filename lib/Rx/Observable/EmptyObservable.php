<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class EmptyObservable extends Observable
{

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $scheduler = $scheduler?: new ImmediateScheduler();

        return $scheduler->schedule(function () use ($observer) {
            $observer->onCompleted();
        });
    }
}
