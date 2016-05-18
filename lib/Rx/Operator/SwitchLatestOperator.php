<?php

namespace Rx\Operator;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class SwitchLatestOperator implements OperatorInterface
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

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $onNext = function ($innerSource) use ($observer, $scheduler) {
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
                        };
                    }
                }
            );

            $innerSubscription = $innerSource->subscribe($innerCallbackObserver, $scheduler);
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

        $subscription = $observable->subscribe($callbackObserver, $scheduler);

        return new BinaryDisposable($subscription, $this->innerSubscription);
    }
}
