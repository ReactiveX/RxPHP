<?php

namespace Rx\Operator;

use Rx\Observer\CallbackObserver;
use Rx\Operator\OperatorInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
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
        return $observable->subscribeCallback(
            function() use ($observer) {
                $observer->onNext(false);
                $observer->onCompleted();
            },
            [$observer, 'onError'],
            function() use ($observer) {
                $observer->onNext(true);
                $observer->onCompleted();
            },
            $scheduler
        );
    }
}