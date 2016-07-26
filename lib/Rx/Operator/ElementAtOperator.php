<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ElementAtOperator
{
    private $index;

    /**
     * ElementAtOperator constructor.
     * @param int $index
     */
    public function __construct ( $index = null ) {
        if (!is_int ($index) || $index < 0) {
            throw new \InvalidArgumentException('index must be an integer greater than or equal to 0');
        }

        $this->index = $index;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $remaining = $this->index;

        $callbackObserver = new CallbackObserver(
            function ($nextValue) use ($observer, &$remaining) {
                if ($remaining === 0) {
                    $observer->onNext($nextValue);
                    $observer->onCompleted();
                }
                $remaining--;
            },
            [$observer, 'onError'],
            function () use (&$remaining, $observer){
                if($remaining !== -1)$observer->onError(new \OutOfRangeException("index out of range"));
            }
        );

        return $observable->subscribe($callbackObserver, $scheduler);
    }
}