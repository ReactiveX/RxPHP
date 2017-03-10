<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ToArrayOperator implements OperatorInterface
{
    /** @var array */
    private $arr = [];

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $cbObserver = new CallbackObserver(
            function ($x) {
                $this->arr[] = $x;
            },
            [$observer, 'onError'],
            function () use ($observer) {
                $observer->onNext($this->arr);
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver);
    }
}
