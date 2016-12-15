<?php

namespace Rx\Observable;

use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;

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

    public function subscribe(ObserverInterface $observer): DisposableInterface
    {
        $value = $this->value;

        $scheduler = Scheduler::getDefault();

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
