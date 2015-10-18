<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;

class NeverObservable extends BaseObservable
{
    protected function doStart($scheduler)
    {
        return new EmptyDisposable();
    }
}
