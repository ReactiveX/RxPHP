<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Notification;
use Rx\Observable;
use Rx\ObserverInterface;

class HotObservable extends Observable
{
    private array $subscriptions = [];
    private array $observers = [];

    public function __construct(private TestScheduler $scheduler, array $messages)
    {
        $this->scheduler   = $scheduler;
        $currentObservable = $this;

        foreach ($messages as $message) {
            $time         = $message->getTime();
            $notification = $message->getValue();

            $schedule = function (Notification $innerNotification) use (&$currentObservable, $scheduler, $time): void {
                $scheduler->scheduleAbsolute($time, function () use (&$currentObservable, $innerNotification): \Rx\Disposable\EmptyDisposable {
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

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $currentObservable = $this;

        $this->observers[]     = $observer;
        $this->subscriptions[] = new Subscription($this->scheduler->getClock());

        $subscriptions = &$this->subscriptions;

        $index     = count($this->subscriptions) - 1;
        $scheduler = $this->scheduler;

        return new CallbackDisposable(function () use (&$currentObservable, $index, $observer, $scheduler, &$subscriptions): void {
            $currentObservable->removeObserver($observer);
            $subscriptions[$index] = new Subscription($subscriptions[$index]->getSubscribed(), $scheduler->getClock());
        });
    }

    public function removeObserver(ObserverInterface $observer): bool
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
    public function getObservers(): array
    {
        return $this->observers;
    }

    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
