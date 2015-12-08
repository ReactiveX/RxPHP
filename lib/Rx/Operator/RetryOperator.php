<?php

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class RetryOperator implements OperatorInterface
{
    private $retryCount;

    /**
     * RetryOperator constructor.
     * @param int $retryCount
     */
    public function __construct($retryCount = -1)
    {
        $this->retryCount = $retryCount;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        $getNewObserver = function () use ($observable, $observer, &$disposable, &$getNewObserver, $scheduler) {
            return new CallbackObserver(
                [$observer, "onNext"],
                function ($error) use ($observable, $observer, &$disposable, &$getNewObserver, $scheduler) {
                    $this->retryCount--;
                    if ($this->retryCount === 0) {
                        $observer->onError($error);
                        return;
                    }
                    $disposable->dispose();
                    $disposable = $observable->subscribe($getNewObserver(), $scheduler);
                },
                function () use ($observer) {
                    $observer->onCompleted();
                    $this->retryCount = 0;
                }
            );
        };

        $disposable = $observable->subscribe($getNewObserver(), $scheduler);

        return new CallbackDisposable(function () use (&$disposable) {
            $disposable->dispose();
        });
    }
}
