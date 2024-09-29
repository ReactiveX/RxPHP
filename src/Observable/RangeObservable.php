<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class RangeObservable extends Observable
{

    public function __construct(
        private readonly int       $start,
        private readonly int       $count,
        private readonly SchedulerInterface $scheduler
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $i = 0;

        return $this->scheduler->scheduleRecursive(function ($reschedule) use (&$observer, &$i): void {
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
