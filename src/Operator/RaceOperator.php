<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class RaceOperator implements OperatorInterface
{

    private bool $hasFirst = false;

    /** @var Observable[] */
    private ?array $observables = [];

    /** @var DisposableInterface[] */
    private array $subscriptions = [];

    private CompositeDisposable $innerSubscription;


    public function __construct()
    {
        $this->innerSubscription = new CompositeDisposable();
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {

        $callbackObserver = new CallbackObserver(
            function (Observable $innerObservable): void {
                $this->observables[] = $innerObservable;
            },
            fn ($err) => $observer->onError($err),
            function () use ($observer): void {

                if (count($this->observables) === 0) {
                    $observer->onCompleted();
                    return;
                }

                foreach ($this->observables as $i => $innerObs) {
                    $subscription = $this->subscribeToResult($innerObs, $observer, $i);

                    $this->subscriptions[] = $subscription;
                    $this->innerSubscription->add($subscription);
                }

                $this->observables = null;
            }
        );

        $subscription = $observable->subscribe($callbackObserver);

        return new BinaryDisposable($subscription, $this->innerSubscription);

    }

    private function subscribeToResult(ObservableInterface $observable, ObserverInterface $observer, $outerIndex): \Rx\DisposableInterface
    {
        return $observable->subscribe(new CallbackObserver(
            function ($value) use ($observer, $outerIndex): void {

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
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()

        ));
    }
}
