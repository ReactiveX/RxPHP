<?php

namespace Rx\Operator;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class RaceOperator implements OperatorInterface
{

    /** @var bool */
    private $hasFirst = false;

    /** @var Observable[] */
    private $observables = [];

    /** @var DisposableInterface[] */
    private $subscriptions = [];

    /** @var CompositeDisposable */
    private $innerSubscription;


    public function __construct()
    {
        $this->innerSubscription = new CompositeDisposable();
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {

        $callbackObserver = new CallbackObserver(
            function (Observable $innerObservable) {
                $this->observables[] = $innerObservable;
            },
            [$observer, 'onError'],
            function () use ($observer, $scheduler) {

                if (count($this->observables) === 0) {
                    $observer->onCompleted();
                    return;
                }

                foreach ($this->observables as $i => $innerObs) {
                    $subscription = $this->subscribeToResult($innerObs, $scheduler, $observer, $i);

                    $this->subscriptions[] = $subscription;
                    $this->innerSubscription->add($subscription);
                }

                $this->observables = null;
            }
        );

        $subscription = $observable->subscribe($callbackObserver, $scheduler);

        return new BinaryDisposable($subscription, $this->innerSubscription);
        
    }
    
    private function subscribeToResult(ObservableInterface $observable, SchedulerInterface $scheduler, ObserverInterface $observer, $outerIndex)
    {
        return $observable->subscribe(new CallbackObserver(
            function ($value) use ($observer, $outerIndex) {

                if (!$this->hasFirst) {
                    $this->hasFirst = true;

                    foreach ($this->subscriptions as $i => $subscription) {
                        if ($i !== $outerIndex) {
                            $subscription->dispose();
                            unset($this->subscriptions[$i]);
                            $this->innerSubscription->remove($subscription);
                        }
                    }
                }

                $observer->onNext($value);

            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']

        ), $scheduler);
    }
}
