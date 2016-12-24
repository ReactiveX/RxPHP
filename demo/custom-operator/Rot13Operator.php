<?php

namespace Vendor\Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\Operator\OperatorInterface;
use Rx\SchedulerInterface;

class Rot13Operator implements OperatorInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        return $observable->subscribe(new CallbackObserver(
            function ($json) use ($observer) {
                $observer->onNext(str_rot13($json));
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        ));
    }
}