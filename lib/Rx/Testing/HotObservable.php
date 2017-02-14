<?php

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\ObserverInterface;

class HotObservable extends Observable
{
    private $scheduler;
    private $messages;
    private $subscriptions = [];

    public function __construct($scheduler, array $messages)
    {
        $this->scheduler   = $scheduler;
        $this->messages    = $messages;
        $currentObservable = $this;

        foreach ($messages as $message) {
            $time         = $message->getTime();
            $notification = $message->getValue();

            $schedule = function ($innerNotification) use (&$currentObservable, $scheduler, $time) {
                $scheduler->scheduleAbsolute($time, function () use (&$currentObservable, $innerNotification) {
                    $observers = $currentObservable->getObservers();

                    foreach ($observers as $observer) {
                        $innerNotification->accept($observer);
                    }

                    return new EmptyDisposable();
                });
            };

            $schedule($notification);
        }
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $currentObservable = $this;

        $this->observers[] = $observer;
        $subscriptions     = &$this->subscriptions;
        $index             = null;

        if (!($observer instanceof MockHigherOrderObserver)) {
            $this->subscriptions[] = new Subscription($this->scheduler->getClock());
            $index = count($this->subscriptions) - 1;
        }

        $scheduler = $this->scheduler;

        return new CallbackDisposable(function () use (&$currentObservable, $index, $observer, $scheduler, &$subscriptions) {
            $currentObservable->removeObserver($observer);
            if (!($observer instanceof MockHigherOrderObserver)) {
                $subscriptions[$index] = new Subscription($subscriptions[$index]->getSubscribed(), $scheduler->getClock());
            }
        });
    }

    public function removeObserver(ObserverInterface $observer)
    {
        $key = array_search($observer, $this->observers, true);

        if (false === $key) {
            return false;
        }

        unset($this->observers[$key]);

        return true;
    }

    /**
     * @internal
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * @return array
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }
}
