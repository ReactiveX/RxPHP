<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class SkipWhileOperator implements OperatorInterface
{
    /** @var callable */
    private $predicate;

    /** @var bool */
    private $isSkipping;

    public function __construct(callable $predicate)
    {
        $this->predicate  = $predicate;
        $this->isSkipping = true;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $callbackObserver = new CallbackObserver(
            function ($value) use ($observer, $observable) {
                try {

                    if ($this->isSkipping) {
                        $this->isSkipping = call_user_func_array($this->predicate, [$value, $observable]);
                    }

                    if (!$this->isSkipping) {
                        $observer->onNext($value);
                    }

                } catch (\Exception $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($callbackObserver, $scheduler);
    }
}
