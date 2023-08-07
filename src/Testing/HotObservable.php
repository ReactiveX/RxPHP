<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Notification;
use Rx\Observable;
use Rx\ObserverInterface;

/**
 * @template T
 * @template-extends Observable<T>
 */
class HotObservable extends Observable
{
    /**
     * @var TestScheduler
     */
    private $scheduler;
    /**
     * @var array<Subscription>
     */
    private $subscriptions = [];
    /**
     * @var array<ObserverInterface>
     */
    private $observers = [];

    /**
     * @param array<Recorded> $messages
     */
    public function __construct(TestScheduler $scheduler, array $messages)
    {
        $this->scheduler   = $scheduler;
        $currentObservable = $this;

        foreach ($messages as $message) {
            $time         = $message->getTime();
            $notification = $message->getValue();

            assert($notification instanceof Notification);

            $schedule = function (Notification $innerNotification) use (&$currentObservable, $scheduler, $time) {
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

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $currentObservable = $this;

        $this->observers[]     = $observer;
        $this->subscriptions[] = new Subscription($this->scheduler->getClock());

        $subscriptions = &$this->subscriptions;

        $index     = count($this->subscriptions) - 1;
        $scheduler = $this->scheduler;

        return new CallbackDisposable(function () use (&$currentObservable, $index, $observer, $scheduler, &$subscriptions) {
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
     *
     * @return array<ObserverInterface>
     */
    public function getObservers(): array
    {
        return $this->observers;
    }

    /**
     * @return array<Subscription>
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
