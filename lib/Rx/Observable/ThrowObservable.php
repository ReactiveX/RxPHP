<?php

namespace Rx\Observable;

use Exception;
use Rx\Disposable\EmptyDisposable;

class ThrowObservable extends BaseObservable
{
    private $exception;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    protected function doStart($scheduler)
    {
        $exception = $this->exception;

        $observers = $this->observers;

        $scheduler->schedule(function() use ($observers, $exception) {
            foreach ($observers as $observer) {
                $observer->onError($exception);
            }
        });

        // todo: real disposable
        return new EmptyDisposable();
    }
}
