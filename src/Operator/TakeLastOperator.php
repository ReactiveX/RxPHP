<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class TakeLastOperator implements OperatorInterface
{
    /** @var integer */
    private $count;

    /** @var array */
    private $items = [];

    public function __construct(int $count)
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Count must be >= 0');
        }

        $this->count = $count;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
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

        return $observable->subscribe($callbackObserver);
    }
}
