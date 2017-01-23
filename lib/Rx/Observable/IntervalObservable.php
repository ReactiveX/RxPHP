<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class IntervalObservable extends Observable
{
    /** @var int */
    private $interval;

    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * @param int $interval
     * @param SchedulerInterface|null $scheduler
     */
    public function __construct($interval, SchedulerInterface $scheduler = null)
    {
        $this->interval  = $interval;
        $this->scheduler = $scheduler;
    }

    /**
     * @inheritdoc
     */
    public function subscribe(ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        if ($this->scheduler !== null) {
            $scheduler = $this->scheduler;
        }
        if ($scheduler === null) {
            throw new \Exception("You must use a scheduler that support non-zero delay.");
        }

        $counter = 0;

        return $scheduler->schedulePeriodic(
            function () use (&$counter, $observer) {
                $observer->onNext($counter++);
            },
            $this->interval, // this is to match RxJS behavior which delays the first item by the interval
            $this->interval
        );
    }
}