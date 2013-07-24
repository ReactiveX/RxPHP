<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;

class EmptyObservable extends BaseObservable
{
    protected function doStart($scheduler)
    {
        $observers = $this->observers;

        $scheduler->schedule(function() use ($observers) {
            foreach ($observers as $observer) {
                $observer->onCompleted();
            }
        });

        //todo: add "real" disposable
        return new EmptyDisposable();
    }
}
