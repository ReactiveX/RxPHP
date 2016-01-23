<?php

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\Observable;
use Rx\ObserverInterface;

/**
 * Class RefCountObservable
 * @package Rx\Observable
 */
class RefCountObservable extends Observable
{
    /** @var \Rx\Observable\ConnectableObservable */
    protected $source;

    /** @var int */
    protected $count;

    /** @var  BinaryDisposable */
    protected $connectableSubscription;

    /**
     * RefCountObservable constructor.
     * @param \Rx\Observable\ConnectableObservable $source
     */
    public function __construct(ConnectableObservable $source)
    {
        $this->source = $source;
        $this->count  = 0;
    }

    /**
     * @param \Rx\ObserverInterface $observer
     * @param null $scheduler
     * @return \Rx\Disposable\CallbackDisposable
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $this->count++;
        $shouldConnect = $this->count === 1;
        $subscription  = $this->source->subscribe($observer, $scheduler);

        if ($shouldConnect) {
            $this->connectableSubscription = $this->source->connect();
        }

        $isDisposed = false;

        return new CallbackDisposable(function () use ($subscription, &$isDisposed) {
            if ($isDisposed) {
                return;
            }

            $isDisposed = true;

            $subscription->dispose();

            $this->count--;

            if ($this->count === 0) {
                $this->connectableSubscription->dispose();
            }
        });
    }
}
