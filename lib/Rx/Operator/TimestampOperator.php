<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;
use Rx\Timestamped;

class TimestampOperator implements OperatorInterface
{
    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * TimestampOperator constructor.
     * @param SchedulerInterface $scheduler
     */
    public function __construct(SchedulerInterface $scheduler = null)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        if ($this->scheduler !== null) {
            $scheduler = $this->scheduler;
        }

        if ($scheduler === null) {
            throw new \Exception("You must use a scheduler with timestamp.");
        }

        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, $scheduler) {
                $observer->onNext(new Timestamped($scheduler->now(), $x));
            },
            [$observer, "onError"],
            [$observer, "onCompleted"]
        ), $scheduler);
    }
}
