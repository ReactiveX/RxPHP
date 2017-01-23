<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class NeverObservable extends Observable
{
    /**
     * @param ObserverInterface $observer
     * @param SchedulerInterface|null $scheduler
     * @return EmptyDisposable
     */
    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return new EmptyDisposable();
    }
}
