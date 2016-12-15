<?php

namespace Rx\Observable;

use Exception;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;

class ErrorObservable extends Observable
{
    private $error;

    public function __construct(Exception $error)
    {
        $this->error = $error;
    }

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {

        $scheduler = Scheduler::getDefault();

        return $scheduler->schedule(function () use ($observer) {
            $observer->onError($this->error);
        });
    }
}
