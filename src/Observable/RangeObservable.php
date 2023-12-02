<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

/**
 * @template T
 * @template-extends Observable<T>
 */
class RangeObservable extends Observable
{
    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $count;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    public function __construct(int $start, int $count, SchedulerInterface $scheduler)
    {
        $this->start     = $start;
        $this->count     = $count;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
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
