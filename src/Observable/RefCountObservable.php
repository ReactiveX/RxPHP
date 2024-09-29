<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;

class RefCountObservable extends Observable
{

    public function __construct(
        private readonly ConnectableObservable $source,
        protected int                          $count = 0,
        protected                              $connectableSubscription = null
    ) {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $this->count++;
        $shouldConnect = $this->count === 1;
        $subscription  = $this->source->subscribe($observer);

        if ($shouldConnect) {
            $this->connectableSubscription = $this->source->connect();
        }

        return new CallbackDisposable(function () use ($subscription): void {
            $subscription->dispose();

            $this->count--;

            if ($this->count === 0) {
                $this->connectableSubscription->dispose();
            }
        });
    }
}
