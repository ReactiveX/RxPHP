<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class TakeWhileOperator implements OperatorInterface
{

    /** @var callable */
    private $predicate;

    public function __construct(callable $predicate)
    {
        $this->predicate = $predicate;
    }

    /**
     * @param ObservableInterface $observable
     * @param ObserverInterface $observer
     * @param SchedulerInterface|null $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $onNext = function ($value) use ($observer) {
            try {
                if (call_user_func($this->predicate, $value)) {
                    $observer->onNext($value);
                } else {
                    $observer->onCompleted();
                }
            } catch (\Exception $e) {
                $observer->onError($e);
            }
        };

        $callbackObserver = new CallbackObserver(
            $onNext,
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($callbackObserver, $scheduler);
    }
}
