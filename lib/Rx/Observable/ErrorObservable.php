<?php

namespace Rx\Observable;

use Exception;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class ErrorObservable extends Observable
{
    private $error;

    public function __construct(Exception $error)
    {
        $this->error = $error;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {

        $scheduler = $scheduler?: new ImmediateScheduler();

        return $scheduler->schedule(function () use ($observer) {
            $observer->onError($this->error);
        });
    }
}
