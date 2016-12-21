<?php

namespace Rx\Observable;

use Interop\Async\Promise;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class FromPromiseObservable extends Observable
{
    private $promise;

    private $scheduler;

    public function __construct(Promise $promise, SchedulerInterface $scheduler)
    {
        $this->promise   = $promise;
        $this->scheduler = $scheduler;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $disposable = new SingleAssignmentDisposable();

        $this->promise->when(function (\Exception $ex = null, $value) use ($disposable, $observer) {

            $this->scheduler->schedule(function () use ($observer, $ex, $value) {
                if ($ex) {
                    $observer->onError($ex);
                    return;
                }

                $observer->onNext($value);
                $observer->onCompleted();

            });
        });

        return $disposable;
    }
}
