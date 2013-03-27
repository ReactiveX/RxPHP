<?php

namespace Rx\Observable;

class ReturnObservable extends BaseObservable
{
    private $value;

    /**
     * @param mixed $value Value to return.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    protected function doStart($scheduler)
    {
        $value     = $this->value;

        $observers = $this->observers;

        $scheduler->schedule(function() use ($observers, $value) {
            foreach ($observers as $observer) {
                $observer->onNext($value);
            }
        });

        $scheduler->schedule(function() use ($observers) {
            foreach ($observers as $observer) {
                $observer->onCompleted();
            }
        });
    }
}
