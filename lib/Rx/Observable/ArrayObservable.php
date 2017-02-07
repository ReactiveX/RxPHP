<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class ArrayObservable extends Observable
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $values    = &$this->data;
        $max       = count($values);
        $keys      = array_keys($values);
        $count     = 0;

        if ($scheduler === null) {
            $scheduler = new ImmediateScheduler();
        }

        return $scheduler->scheduleRecursive(function ($reschedule) use (&$observer, &$values, $max, &$count, $keys) {
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
