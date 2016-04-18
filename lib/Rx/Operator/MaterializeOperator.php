<?php

namespace Rx\Operator;

use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class MaterializeOperator implements OperatorInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer) {
                $observer->onNext(new OnNextNotification($x));
            },
            function ($error) use ($observer) {
                $observer->onNext(new OnErrorNotification($error));
                $observer->onCompleted();
            },
            function () use ($observer) {
                $observer->onNext(new OnCompletedNotification());
                $observer->onCompleted();
            }
        ), $scheduler);
    }
}
