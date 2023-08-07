<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

/**
 * @template T
 * @template-extends Observable<T>
 */
class ReturnObservable extends Observable
{
    /**
     * @var T
     */
    private $value;

    /**
     * @var SchedulerInterface
     */
    private $scheduler;

    /**
     * @param T $value
     */
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
