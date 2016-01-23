<?php

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class RepeatOperator implements OperatorInterface
{
    private $repeatCount;

    /**
     * @param $repeatCount
     */
    public function __construct($repeatCount = -1)
    {
        if ($repeatCount < 0) {
            $repeatCount = -1;
        }

        $this->repeatCount = $repeatCount;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        $completeCount = 0;

        $disposable = new SerialDisposable();

        $subscribe = function () use (&$disposable, $observable, $observer, &$completeCount, $scheduler, &$subscribe) {
            $disposable->setDisposable($observable->subscribe(new CallbackObserver(
                [$observer, "onNext"],
                [$observer, "onError"],
                function () use (&$completeCount, $observable, $observer, &$disposable, &$subscribe, $scheduler) {
                    $completeCount++;
                    if ($this->repeatCount === -1 || $completeCount < $this->repeatCount) {
                        $subscribe();
                    }
                    if ($completeCount === $this->repeatCount) {
                        $observer->onCompleted();
                        return;
                    }
                }
            ), $scheduler));
        };

        $subscribe();

        return new CallbackDisposable(function () use (&$disposable) {
            $disposable->dispose();
        });
    }
}
