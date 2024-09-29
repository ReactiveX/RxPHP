<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class RepeatOperator implements OperatorInterface
{
    public function __construct(private int $repeatCount = -1)
    {
        if ($repeatCount < 0) {
            $repeatCount = -1;
        }

        $this->repeatCount = $repeatCount;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $completeCount = 0;

        $disposable = new SerialDisposable();

        $subscribe = function () use (&$disposable, $observable, $observer, &$completeCount, &$subscribe): void {
            $disposable->setDisposable($observable->subscribe(new CallbackObserver(
                fn ($x) => $observer->onNext($x),
                fn ($err) => $observer->onError($err),
                function () use (&$completeCount, $observable, $observer, &$disposable, &$subscribe): void {
                    $completeCount++;
                    if ($this->repeatCount === -1 || $completeCount < $this->repeatCount) {
                        $subscribe();
                    }
                    if ($completeCount === $this->repeatCount) {
                        $observer->onCompleted();
                        return;
                    }
                }
            )));
        };

        $subscribe();

        return new CallbackDisposable(function () use (&$disposable): void {
            $disposable->dispose();
        });
    }
}
