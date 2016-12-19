<?php

namespace Rx\Observable;

use Exception;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class ErrorObservable extends Observable
{
    private $error;
    private $scheduler;

    public function __construct(Exception $error, SchedulerInterface $scheduler = null)
    {
        $this->error     = $error;
        $this->scheduler = $scheduler ?: Scheduler::getImmediate();
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->scheduler->schedule(function () use ($observer) {
            $observer->onError($this->error);
        });
    }
}
