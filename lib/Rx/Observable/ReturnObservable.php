<?php

namespace Rx\Observable;

use Rx\Disposable\CompositeDisposable;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class ReturnObservable extends Observable
{
    private $value;

    /**
     * @param mixed $value Value to return.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $value     = $this->value;

        $scheduler = $scheduler ?: new ImmediateScheduler();

        $disposable = new CompositeDisposable();

        $disposable->add($scheduler->schedule(function () use ($observer, $value) {
            $observer->onNext($value);
        }));

        $disposable->add($scheduler->schedule(function () use ($observer) {
            $observer->onCompleted();
        }));

        return $disposable;
    }
}
