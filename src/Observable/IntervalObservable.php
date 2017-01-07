<?php

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class IntervalObservable extends Observable
{
    private $interval;

    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * IntervalObservable constructor.
     * @param $interval
     * @param SchedulerInterface $scheduler
     */
    public function __construct(int $interval, SchedulerInterface $scheduler)
    {
        $this->interval  = $interval;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $counter = 0;

        return $this->scheduler->schedulePeriodic(
            function () use (&$counter, $observer) {
                $observer->onNext($counter++);
            },
            $this->interval, // this is to match RxJS behavior which delays the first item by the interval
            $this->interval
        );
    }
}
