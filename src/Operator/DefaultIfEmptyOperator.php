<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class DefaultIfEmptyOperator implements OperatorInterface
{
    public function __construct(
        private ObservableInterface $observable,
        private bool $passThrough = false
    ) {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposable = new SerialDisposable();
        $cbObserver = new CallbackObserver(
            function ($x) use ($observer): void {
                $this->passThrough = true;
                $observer->onNext($x);
            },
            fn ($err) => $observer->onError($err),
            function () use ($observer, $disposable): void {
                if (!$this->passThrough) {
                    $disposable->setDisposable($this->observable->subscribe($observer));
                    return;
                }

                $observer->onCompleted();
            }
        );

        $subscription = $observable->subscribe($cbObserver);

        $disposable->setDisposable($subscription);

        return $disposable;
    }
}
