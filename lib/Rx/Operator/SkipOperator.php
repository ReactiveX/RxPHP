<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class SkipOperator implements OperatorInterface
{
    /**
     * @var integer
     */
    private $count;

    public function __construct($count)
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Count must be >= 0');
        }

        $this->count = $count;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $remaining = $this->count;

        $cbObserver = new CallbackObserver(
            function ($nextValue) use ($observer, &$remaining) {
                if ($remaining <= 0) {
                    $observer->onNext($nextValue);
                } else {
                    $remaining--;
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($cbObserver, $scheduler);
    }
}
