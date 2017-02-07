<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class MaterializeOperator implements OperatorInterface
{
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer) {
                $observer->onNext(new OnNextNotification($x));
            },
            function ($error) use ($observer) {
                $observer->onNext(new OnErrorNotification($error));
                $observer->onCompleted();
            },
            function () use ($observer) {
                $observer->onNext(new OnCompletedNotification());
                $observer->onCompleted();
            }
        ));
    }
}
