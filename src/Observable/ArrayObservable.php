<?php

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class ArrayObservable extends Observable
{
    private $data;

    private $scheduler;

    public function __construct(array $data, SchedulerInterface $scheduler = null)
    {
        $this->data      = $data;
        $this->scheduler = $scheduler ?: Scheduler::getDefault();
    }

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        $values = &$this->data;
        $max    = count($values);
        $keys   = array_keys($values);
        $count  = 0;

        return $this->scheduler->scheduleRecursive(function ($reschedule) use (&$observer, &$values, $max, &$count, $keys) {
            if ($count < $max) {
                $observer->onNext($values[$keys[$count]]);
                $count++;
                if ($count >= 1) {
                    $reschedule();
                    return;
                }
            }
            $observer->onCompleted();
        });
    }
}
