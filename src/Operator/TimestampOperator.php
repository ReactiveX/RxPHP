<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;
use Rx\Timestamped;

class TimestampOperator implements OperatorInterface
{
    private $scheduler;

    public function __construct(SchedulerInterface $scheduler = null)
    {
        $this->scheduler = $scheduler;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer) {
                $observer->onNext(new Timestamped($this->scheduler->now(), $x));
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        ));
    }
}
