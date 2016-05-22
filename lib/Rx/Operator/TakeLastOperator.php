<?php

namespace Rx\Operator;

use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class TakeLastOperator implements OperatorInterface
{
    /** @var integer */
    private $count;

    /** @var array */
    private $items = [];

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
        $callbackObserver = new CallbackObserver(
            function ($nextValue) use ($observer) {
                $this->items[] = $nextValue;

                if (count($this->items) > $this->count) {
                    array_shift($this->items);
                }
            },
            [$observer, 'onError'],
            function () use ($observer) {

                while (count($this->items) > 0) {
                    $observer->onNext(array_shift($this->items));
                }

                $observer->onCompleted();
            }
        );

        return $observable->subscribe($callbackObserver, $scheduler);
    }
}
