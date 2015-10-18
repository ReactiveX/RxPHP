<?php

namespace Rx\Operator;

use Rx\Observable\BaseObservable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DeferOperator implements OperatorInterface
{

    /**
     * @var Callable
     */
    private $factory;

    function __construct($factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $factory = $this->factory;

        try {
            $result = $factory();

            return $result->subscribe($observer);
        } catch (\Exception $e) {
            return BaseObservable::throwError($e)->subscribe($observer);
        }
    }
}
