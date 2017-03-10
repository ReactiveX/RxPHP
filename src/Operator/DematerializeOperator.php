<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Notification;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class DematerializeOperator implements OperatorInterface
{
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->subscribe(new CallbackObserver(
            function (Notification $x) use ($observer) {
                $x->accept($observer);
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        ));
    }
}
