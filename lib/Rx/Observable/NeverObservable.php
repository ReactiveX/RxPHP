<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class NeverObservable extends BaseObservable
{

    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return new EmptyDisposable();
    }
}
