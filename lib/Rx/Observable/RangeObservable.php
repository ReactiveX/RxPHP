<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class RangeObservable extends Observable
{
    /** @var integer */
    private $start;

    /** @var integer */
    private $count;

    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * SkipLastOperator constructor.
     * @param $start
     * @param $count
     * @param SchedulerInterface $scheduler
     */
    public function __construct($start, $count, SchedulerInterface $scheduler = null)
    {
        if (!is_int($start) || !is_int($count)) {
            throw new \InvalidArgumentException("'start' and 'count' must be an integer");
        }

        $this->start     = $start;
        $this->count     = $count;
        $this->scheduler = $scheduler;

    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {

        if ($this->scheduler !== null) {
            $scheduler = $this->scheduler;
        }

        if ($scheduler === null) {
            $scheduler = new ImmediateScheduler();
        }

        $i = 0;

        return $scheduler->scheduleRecursive(function ($reschedule) use (&$observer, &$i) {
            if ($i < $this->count) {
                $observer->onNext($this->start + $i);
                $i++;
                $reschedule();

            } else {
                $observer->onCompleted();
            }
        });
    }
}
