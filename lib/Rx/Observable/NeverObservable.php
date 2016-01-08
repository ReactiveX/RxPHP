<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class NeverObservable extends Observable
{

    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return new EmptyDisposable();
    }
}
