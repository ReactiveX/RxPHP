<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ToArrayOperator implements OperatorInterface
{
    /** @var array  */
    private $arr = [];

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $cbObserver = new CallbackObserver(
            function ($x) {
                $this->arr[] = $x;
            },
            [$observer, 'onError'],
            function () use ($observer) {
                $observer->onNext($this->arr);
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver, $scheduler);
    }
}
