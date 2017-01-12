<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class IsEmptyOperator implements OperatorInterface
{
    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $cbObserver = new CallbackObserver(
            function() use ($observer) {
                $observer->onNext(false);
                $observer->onCompleted();
            },
            [$observer, 'onError'],
            function() use ($observer) {
                $observer->onNext(true);
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver, $scheduler);
    }
}