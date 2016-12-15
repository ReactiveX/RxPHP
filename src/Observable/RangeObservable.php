<?php

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
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
     * @throws \InvalidArgumentException
     */
    public function __construct(int $start, int $count, SchedulerInterface $scheduler = null)
    {
        $this->start     = $start;
        $this->count     = $count;
        $this->scheduler = $scheduler ?: Scheduler::getDefault();
    }

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        $i = 0;

        return $this->scheduler->scheduleRecursive(function ($reschedule) use (&$observer, &$i) {
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
