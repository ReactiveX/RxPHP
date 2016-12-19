<?php

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class TimerObservable extends Observable
{
    /** @var int */
    private $dueTime;

    /** @var SchedulerInterface */
    private $scheduler;

    public function __construct(int $dueTime, SchedulerInterface $scheduler = null)
    {
        $this->dueTime   = $dueTime;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $scheduler = $this->scheduler ?? Scheduler::getAsync();

        return $scheduler->schedule(
            function () use ($observer) {
                $observer->onNext(0);
                $observer->onCompleted();
            },
            $this->dueTime
        );
    }
}
