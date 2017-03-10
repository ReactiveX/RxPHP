<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;

class NeverObservable extends Observable
{
    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return new EmptyDisposable();
    }
}
