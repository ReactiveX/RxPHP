<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class MergeAllOperator implements OperatorInterface
{
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $group              = new CompositeDisposable();
        $isStopped          = false;
        $sourceSubscription = new SingleAssignmentDisposable();

        $group->add($sourceSubscription);

        $callbackObserver = new CallbackObserver(
            function (ObservableInterface $innerSource) use (&$group, &$isStopped, $observer) {
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
                    ))
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

        $subscription = $observable->subscribe($callbackObserver);

        $sourceSubscription->setDisposable($subscription);

        return $group;
    }
}
