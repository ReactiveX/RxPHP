<?php

namespace Rx\Observable;

use Exception;
use Rx\Disposable\EmptyDisposable;

class ErrorObservable extends BaseObservable
{
    private $error;

    public function __construct(Exception $error)
    {
        $this->error = $error;
    }

    protected function doStart($scheduler)
    {
        $observers = $this->observers;

        $scheduler->schedule(function() use ($observers) {
            foreach ($observers as $observer) {
                $observer->onError($this->error);
            }
        });

        //todo: add "real" disposable
        return new EmptyDisposable();
    }
}
