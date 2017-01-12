<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ReturnObservable extends Observable
{
    private $value;
    private $scheduler;

    public function __construct($value, SchedulerInterface $scheduler)
    {
        $this->value     = $value;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $disposable = new CompositeDisposable();

        $disposable->add($this->scheduler->schedule(function () use ($observer) {
            $observer->onNext($this->value);
        }));

        $disposable->add($this->scheduler->schedule(function () use ($observer) {
            $observer->onCompleted();
        }));

        return $disposable;
    }
}
