<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class RetryOperator implements OperatorInterface
{
    public function __construct(private int $retryCount = -1)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposable = new SerialDisposable();

        $getNewObserver = function () use ($observable, $observer, $disposable, &$getNewObserver): \Rx\Observer\CallbackObserver {
            return new CallbackObserver(
                fn ($x) => $observer->onNext($x),
                function ($error) use ($observable, $observer, $disposable, &$getNewObserver): void {
                    $this->retryCount--;
                    if ($this->retryCount === 0) {
                        $observer->onError($error);
                        return;
                    }

                    $subscription = $observable->subscribe($getNewObserver());
                    $disposable->setDisposable($subscription);
                },
                function () use ($observer): void {
                    $observer->onCompleted();
                    $this->retryCount = 0;
                }
            );
        };

        $subscription = $observable->subscribe($getNewObserver());
        $disposable->setDisposable($subscription);

        return new CallbackDisposable(function () use (&$disposable): void {
            $disposable->dispose();
        });
    }
}
