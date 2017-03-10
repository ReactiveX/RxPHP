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
    private $repeatCount;

    public function __construct(int $repeatCount = -1)
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

        $subscribe = function () use (&$disposable, $observable, $observer, &$completeCount, &$subscribe) {
            $disposable->setDisposable($observable->subscribe(new CallbackObserver(
                [$observer, 'onNext'],
                [$observer, 'onError'],
                function () use (&$completeCount, $observable, $observer, &$disposable, &$subscribe) {
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

        return new CallbackDisposable(function () use (&$disposable) {
            $disposable->dispose();
        });
    }
}
