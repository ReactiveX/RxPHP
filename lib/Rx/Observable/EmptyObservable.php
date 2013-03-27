<?php

namespace Rx\Observable;

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
    }
}
