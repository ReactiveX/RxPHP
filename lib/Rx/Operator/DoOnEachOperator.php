<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DoOnEachOperator implements OperatorInterface
{

    protected $observerOrOnNext;
    protected $onError;
    protected $onCompleted;

    function __construct($observerOrOnNext, $onError = null, $onCompleted = null)
    {
        $this->observerOrOnNext = $observerOrOnNext;
        $this->onError          = $onError;
        $this->onCompleted      = $onCompleted;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $tapObserver = is_callable($this->observerOrOnNext) ? new CallbackObserver($this->observerOrOnNext, $this->onError, $this->onCompleted) : $this->observerOrOnNext;

        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, $tapObserver) {
                try {
                    $tapObserver->onNext($x);
                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
                $observer->onNext($x);

            },
            function ($err) use ($observer, $tapObserver) {
                try {
                    $tapObserver->onError($err);
                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
                $observer->onError($err);
            },
            function () use ($observer, $tapObserver) {
                try {
                    $tapObserver->onCompleted();
                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
                $observer->onCompleted();
            })
        );
    }
}
