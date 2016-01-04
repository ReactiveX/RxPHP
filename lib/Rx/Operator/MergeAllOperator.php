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
     * @var ObservableInterface
     */
    private $sources;

    public function __construct(ObservableInterface $sources)
    {
        $this->sources = $sources;
    }

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

        $sourceSubscription->setDisposable(
            $this->sources->subscribe(new CallbackObserver(
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
                function ($error) use ($observer) {
                    $observer->onError($error);
                },
                function () use (&$group, &$isStopped, $observer) {
                    $isStopped = true;
                    if ($group->count() === 1) {
                        $observer->onCompleted();
                    }
                }
            ), $scheduler)
        );

        return $group;
    }
}
