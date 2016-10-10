<?php

namespace Rx\Operator;

use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DeferOperator implements OperatorInterface
{

    /**
     * @var Callable
     */
    private $factory;

    public function __construct(callable $factory)
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
        } catch (\Exception $e) {
            return Observable::error($e)->subscribe($observer, $scheduler);
        }
        
        return $result->subscribe($observer, $scheduler);
    }
}
