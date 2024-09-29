<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ToArrayOperator implements OperatorInterface
{
    private array $arr = [];

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $cbObserver = new CallbackObserver(
            function ($x): void {
                $this->arr[] = $x;
            },
            fn ($err) => $observer->onError($err),
            function () use ($observer): void {
                $observer->onNext($this->arr);
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver);
    }
}
