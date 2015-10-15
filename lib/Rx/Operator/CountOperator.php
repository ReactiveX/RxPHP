<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class CountOperator implements OperatorInterface {
    private $count = 0;
    private $predicate;

    /**
     * Count constructor.
     * @param $predicate
     */
    public function __construct($predicate = null)
    {
        $this->predicate = $predicate;
    }

    /**
     * @inheritDoc
     */
    public function call(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        return $observable->subscribe(new CallbackObserver(
            function ($x) {
                if ($this->predicate === null) {
                    $this->count++;
                    return;
                }

                $predicate = $this->predicate;
                if (call_user_func($predicate, $x)) {
                    $this->count++;
                }
            },
            [$observer, 'onError'],
            function () use ($observer) {
                $observer->onNext($this->count);
                $observer->onCompleted();
            }
        ));
    }
}