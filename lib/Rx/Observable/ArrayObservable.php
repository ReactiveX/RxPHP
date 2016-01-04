<?php

namespace Rx\Observable;

class ArrayObservable extends BaseObservable
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function doStart($scheduler)
    {
        $values    = &$this->data;
        $observers = &$this->observers;
        $max       = count($values);
        $keys      = array_keys($values);
        $count     = 0;

        return $scheduler->scheduleRecursive(function ($reschedule) use (&$observers, &$values, $max, &$count, $keys) {

            if ($count < $max) {
                foreach ($observers as $observer) {
                    $observer->onNext($values[$keys[$count]]);
                }

                $count++;

                if ($count >= 1) {
                    $reschedule();

                    return;
                }
            }

            foreach ($observers as $observer) {
                $observer->onCompleted();
            }

        });
    }
}
