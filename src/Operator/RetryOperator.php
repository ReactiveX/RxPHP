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
    private $retryCount;

    public function __construct(int $retryCount = -1)
    {
        $this->retryCount = $retryCount;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposable = new SerialDisposable();

        $getNewObserver = function () use ($observable, $observer, $disposable, &$getNewObserver) {
            return new CallbackObserver(
                [$observer, 'onNext'],
                function ($error) use ($observable, $observer, $disposable, &$getNewObserver) {
                    $this->retryCount--;
                    if ($this->retryCount === 0) {
                        $observer->onError($error);
                        return;
                    }

                    $subscription = $observable->subscribe($getNewObserver());
                    $disposable->setDisposable($subscription);
                },
                function () use ($observer) {
                    $observer->onCompleted();
                    $this->retryCount = 0;
                }
            );
        };

        $subscription = $observable->subscribe($getNewObserver());
        $disposable->setDisposable($subscription);

        return new CallbackDisposable(function () use (&$disposable) {
            $disposable->dispose();
        });
    }
}
