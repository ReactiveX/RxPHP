<?php

namespace Rx\Observable;

use Exception;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class ErrorObservable extends Observable
{
    private $error;

    public function __construct(Exception $error)
    {
        $this->error = $error;
    }

    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {

        $scheduler = $scheduler?: Scheduler::getDefault();

        return $scheduler->schedule(function () use ($observer) {
            $observer->onError($this->error);
        });
    }
}
