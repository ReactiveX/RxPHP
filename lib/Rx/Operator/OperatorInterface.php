<?php

namespace Rx\Operator;


use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;


interface OperatorInterface
{

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null);

}