<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;
use Rx\Timestamped;

final class TimestampOperator implements OperatorInterface
{
    public function __construct(private readonly null|SchedulerInterface $scheduler = null)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer): void {
                $observer->onNext(new Timestamped($this->scheduler->now(), $x));
            },
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()
        ));
    }
}
