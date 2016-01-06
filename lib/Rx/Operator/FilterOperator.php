<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

/**
 * Class FilterOperator
 * @package Rx\Operator
 */
class FilterOperator implements OperatorInterface
{
    /** @var callable */
    private $predicate;

    /**
     * FilterOperator constructor.
     * @param callable $predicate
     */
    public function __construct(callable $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $selectObserver = new CallbackObserver(
            function ($nextValue) use ($observer) {
                $shouldFire = false;
                try {
                    $shouldFire = call_user_func($this->predicate, $nextValue);
                } catch (\Exception $e) {
                    $observer->onError($e);
                }

                if ($shouldFire) {
                    $observer->onNext($nextValue);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($selectObserver, $scheduler);
    }
}
