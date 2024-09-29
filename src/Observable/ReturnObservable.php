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
    public function __construct(
        private $value,
        private SchedulerInterface $scheduler
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $disposable = new CompositeDisposable();

        $disposable->add($this->scheduler->schedule(function () use ($observer): void {
            $observer->onNext($this->value);
        }));

        $disposable->add($this->scheduler->schedule(function () use ($observer): void {
            $observer->onCompleted();
        }));

        return $disposable;
    }
}
