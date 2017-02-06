<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class EmptyObservable extends Observable
{
    /**
     * @inheritdoc
     */
    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $scheduler = $scheduler?: new ImmediateScheduler();

        return $scheduler->schedule(function () use ($observer) {
            $observer->onCompleted();
        });
    }
}
