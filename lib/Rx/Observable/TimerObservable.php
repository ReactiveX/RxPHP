<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class TimerObservable extends Observable
{
    /** @var int */
    private $dueTime;

    /** @var SchedulerInterface */
    private $scheduler;

    public function __construct($dueTime, SchedulerInterface $scheduler = null)
    {

        if (!is_int($dueTime)) {
            throw new \InvalidArgumentException("'dueTime' must be an integer");
        }

        $this->dueTime   = $dueTime;
        $this->scheduler = $scheduler;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        if ($this->scheduler !== null) {
            $scheduler = $this->scheduler;
        }
        if ($scheduler === null) {
            throw new \Exception("You must use a scheduler that support non-zero delay.");
        }

        return $scheduler->schedule(
            function () use ($observer) {
                $observer->onNext(0);
                $observer->onCompleted();
            },
            $this->dueTime
        );
    }
}
