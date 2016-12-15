<?php

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;

class EmptyObservable extends Observable
{

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        $scheduler = Scheduler::getDefault();

        return $scheduler->schedule(function () use ($observer) {
            $observer->onCompleted();
        });
    }
}
