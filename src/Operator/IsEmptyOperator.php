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
            function() use ($observer) {
                $observer->onNext(false);
                $observer->onCompleted();
            },
            [$observer, 'onError'],
            function() use ($observer) {
                $observer->onNext(true);
                $observer->onCompleted();
            }
        );
    }

}