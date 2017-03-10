<?php

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObserverInterface;

class ColdObservable extends Observable
{
    private $scheduler;
    private $messages;
    private $subscriptions = [];

    public function __construct($scheduler, $messages = [])
    {
        $this->scheduler = $scheduler;
        $this->messages  = $messages;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $scheduler         = $scheduler ?: $this->scheduler;
        $currentObservable = $this;
        $disposable        = new CompositeDisposable();
        $isDisposed        = false;
        $index             = null;

        if (!($observer instanceof MockHigherOrderObserver)) {
            $this->subscriptions[] = new Subscription($scheduler->getClock());
            $index = count($this->subscriptions) - 1;
        }

        foreach ($this->messages as $message) {
            $notification = $message->getValue();
            $time         = $message->getTime();

            $schedule = function ($innerNotification) use (&$disposable, &$currentObservable, $observer, $scheduler, $time, &$isDisposed) {
                $disposable->add($scheduler->scheduleRelativeWithState(null, $time, function () use ($observer, $innerNotification, &$isDisposed) {
                    if (!$isDisposed) {
                        $innerNotification->accept($observer);
                    }
                    return new EmptyDisposable();
                }));
            };

            $schedule($notification);
        }

        $subscriptions = &$this->subscriptions;

        return new CallbackDisposable(function () use (&$currentObservable, $index, $observer, $scheduler, &$subscriptions, &$isDisposed) {
            $isDisposed            = true;
            if (!($observer instanceof MockHigherOrderObserver)) {
                $subscriptions[$index] = new Subscription($subscriptions[$index]->getSubscribed(), $scheduler->getClock());
            }
        });

    }

    public function getSubscriptions()
    {
        return $this->subscriptions;
    }
}
