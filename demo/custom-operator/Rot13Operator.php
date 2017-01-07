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
            function ($json) use ($observer) {
                $observer->onNext(str_rot13($json));
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );
    }
}