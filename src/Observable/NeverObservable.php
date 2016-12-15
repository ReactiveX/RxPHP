<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;

class NeverObservable extends Observable
{
    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        return new EmptyDisposable();
    }
}
