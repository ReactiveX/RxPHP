<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\AsyncSchedulerInterface;

class IntervalObservable extends Observable
{
    private $interval;

    /** @var AsyncSchedulerInterface */
    private $scheduler;

    /**
     * IntervalObservable constructor.
     * @param $interval
     * @param AsyncSchedulerInterface $scheduler
     */
    public function __construct(int $interval, AsyncSchedulerInterface $scheduler)
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
