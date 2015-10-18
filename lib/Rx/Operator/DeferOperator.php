<?php


namespace Rx\Operator;


use React\Promise\PromiseInterface;
use Rx\Observable\BaseObservable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\React\Promise;
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

            if ($result instanceof PromiseInterface) {
                $result = Promise::toObservable($result);
            }

            return $result->subscribe($observer);
        } catch (\Exception $e) {
            return BaseObservable::throwError($e)->subscribe($observer);
        }
    }
}
