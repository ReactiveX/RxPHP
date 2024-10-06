<?php

namespace Vendor\Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Operator\OperatorInterface;

class Rot13Operator implements OperatorInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->subscribe(
            fn ($json) => $observer->onNext(str_rot13($json)),
            fn ($e) => $observer->onError($e),
            fn () => $observer->onCompleted()
        );
    }
}