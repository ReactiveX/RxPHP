<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class CountOperator implements OperatorInterface
{
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
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    )
    {
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer) {
                if ($this->predicate === null) {
                    $this->count++;
                    return;
                }
                try {
                    $predicate = $this->predicate;
                    if (call_user_func($predicate, $x)) {
                        $this->count++;
                    }
                } catch (\Exception $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use ($observer) {
                $observer->onNext($this->count);
                $observer->onCompleted();
            }
        ), $scheduler);
    }
}