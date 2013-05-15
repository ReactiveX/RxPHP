<?php

namespace Rx\Observable;

use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;

class ArrayObservable extends BaseObservable
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    protected function doStart($scheduler)
    {
        $observers = $this->observers;

        $values     = $this->data;

        $observers = &$this->observers;

        return $scheduler->scheduleRecursive(function($reschedule) use (&$observers, &$values) {
            if (count($values) > 0) {
                $value = array_shift($values);

                foreach ($observers as $observer) {
                    $observer->onNext($value);
                }

                $reschedule();

                return;
            }

            foreach ($observers as $observer) {
                $observer->onCompleted();
            }
        });

        //todo: add "real" disposable
        return new EmptyDisposable();
    }
}
