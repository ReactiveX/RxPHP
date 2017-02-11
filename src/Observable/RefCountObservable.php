<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\Disposable\BinaryDisposable;
use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
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

    public function __construct(ConnectableObservable $source)
    {
        $this->source = $source;
        $this->count  = 0;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $this->count++;
        $shouldConnect = $this->count === 1;
        $subscription  = $this->source->subscribe($observer);

        if ($shouldConnect) {
            $this->connectableSubscription = $this->source->connect();
        }

        return new CallbackDisposable(function () use ($subscription) {
            $subscription->dispose();

            $this->count--;

            if ($this->count === 0) {
                $this->connectableSubscription->dispose();
            }
        });
    }
}
