<?php

namespace Rx\Operator;

use Rx\Notification;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DematerializeOperator implements OperatorInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return $observable->subscribe(new CallbackObserver(
            function (Notification $x) use ($observer) {
                $x->accept($observer);
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        ));
    }
}
