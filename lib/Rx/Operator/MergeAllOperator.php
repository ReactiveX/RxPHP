<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class MergeAllOperator implements OperatorInterface
{

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $group              = new CompositeDisposable();
        $isStopped          = false;
        $sourceSubscription = new SingleAssignmentDisposable();

        $group->add($sourceSubscription);

        $callbackObserver = new CallbackObserver(
            function (ObservableInterface $innerSource) use (&$group, &$isStopped, $observer, &$scheduler) {
                $innerSubscription = new SingleAssignmentDisposable();
                $group->add($innerSubscription);

                $innerSubscription->setDisposable(
                    $innerSource->subscribe(new CallbackObserver(
                        function ($nextValue) use ($observer) {
                            $observer->onNext($nextValue);
                        },
                        function ($error) use ($observer) {
                            $observer->onError($error);
                        },
                        function () use (&$group, &$innerSubscription, &$isStopped, $observer) {
                            $group->remove($innerSubscription);

                            if ($isStopped && $group->count() === 1) {
                                $observer->onCompleted();
                            }
                        }
                    ), $scheduler)
                );
            },
            [$observer, 'onError'],
            function () use (&$group, &$isStopped, $observer) {
                $isStopped = true;
                if ($group->count() === 1) {
                    $observer->onCompleted();
                }
            }
        );

        $subscription = $observable->subscribe($callbackObserver, $scheduler);

        $sourceSubscription->setDisposable($subscription);

        return $group;
    }
}
