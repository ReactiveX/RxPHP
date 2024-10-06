<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

final class IsEmptyOperator implements OperatorInterface
{

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->subscribe(
            function() use ($observer): void {
                $observer->onNext(false);
                $observer->onCompleted();
            },
            fn ($err) => $observer->onError($err),
            function() use ($observer): void {
                $observer->onNext(true);
                $observer->onCompleted();
            }
        );
    }

}