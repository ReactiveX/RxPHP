<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ErrorObservable extends Observable
{
    private $error;
    private $scheduler;

    public function __construct(\Throwable $error, SchedulerInterface $scheduler)
    {
        $this->error     = $error;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        return $this->scheduler->schedule(function () use ($observer) {
            $observer->onError($this->error);
        });
    }
}
