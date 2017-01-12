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
    /** @var bool */
    private $hasLatest;

    /** @var bool */
    private $isStopped;

    /** @var int */
    private $latest;

    /** @var SerialDisposable */
    private $innerSubscription;

    public function __construct()
    {
        $this->hasLatest         = false;
        $this->isStopped         = false;
        $this->latest            = 0;
        $this->innerSubscription = new SerialDisposable();
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $onNext = function ($innerSource) use ($observer) {
            $innerDisposable = new SingleAssignmentDisposable();

            $id = ++$this->latest;

            $this->hasLatest = true;
            $this->innerSubscription->setDisposable($innerDisposable);

            $innerCallbackObserver = new CallbackObserver(
                function ($x) use ($id, $observer) {
                    if ($this->latest === $id) {
                        $observer->onNext($x);
                    }
                },
                function ($e) use ($id, $observer) {
                    if ($this->latest === $id) {
                        $observer->onError($e);
                    }
                },
                function () use ($id, $observer) {
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
            [$observer, 'onError'],
            function () use ($observer) {
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
