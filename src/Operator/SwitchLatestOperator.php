<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class SwitchLatestOperator implements OperatorInterface
{
    private bool $hasLatest = false;

    private bool $isStopped = false;

    private int $latest = 0;

    private SerialDisposable $innerSubscription;

    public function __construct()
    {
        $this->innerSubscription = new SerialDisposable();
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $onNext = function ($innerSource) use ($observer): void {
            $innerDisposable = new SingleAssignmentDisposable();

            $id = ++$this->latest;

            $this->hasLatest = true;
            $this->innerSubscription->setDisposable($innerDisposable);

            $innerCallbackObserver = new CallbackObserver(
                function ($x) use ($id, $observer): void {
                    if ($this->latest === $id) {
                        $observer->onNext($x);
                    }
                },
                function ($e) use ($id, $observer): void {
                    if ($this->latest === $id) {
                        $observer->onError($e);
                    }
                },
                function () use ($id, $observer): void {
                    if ($this->latest === $id) {
                        $this->hasLatest = false;
                        if ($this->isStopped) {
                            $observer->onCompleted();
                        }
                    }
                }
            );

            $innerSubscription = $innerSource->subscribe($innerCallbackObserver);
            $innerDisposable->setDisposable($innerSubscription);
        };

        $callbackObserver = new CallbackObserver(
            $onNext,
            fn ($err) => $observer->onError($err),
            function () use ($observer): void {
                $this->isStopped = true;
                if (!$this->hasLatest) {
                    $observer->onCompleted();
                }
            }
        );

        $subscription = $observable->subscribe($callbackObserver);

        return new BinaryDisposable($subscription, $this->innerSubscription);
    }
}
