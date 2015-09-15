<?php


namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;


class AsObservableOperator implements OperatorInterface
{
    /**
     * @inheritDoc
     */
    public function call(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return $observable->subscribe($observer);
    }
}