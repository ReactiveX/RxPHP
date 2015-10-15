<?php


namespace Rx\Operator;


use Rx\Disposable\EmptyDisposable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class NeverOperator implements OperatorInterface
{


    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return new EmptyDisposable();
    }
}