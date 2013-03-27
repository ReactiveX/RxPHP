<?php

namespace Rx\Observable;

use Rx\ObserverInterface;

class ArrayObservable extends BaseObservable
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function doStart($scheduler)
    {
        $observers = $this->observers;

        foreach ($this->data as $value) {
            $scheduler->schedule(function() use ($observers, $value) {
                foreach ($observers as $observer) {
                    $observer->onNext($value);
                }
            });
        }

        $scheduler->schedule(function() use ($observers) {
            foreach ($observers as $observer) {
                $observer->onCompleted();
            }
        });
    }
}
