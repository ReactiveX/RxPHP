<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Notification;
use Rx\Observable;
use Rx\ObserverInterface;

class ColdObservable extends Observable
{
    private $scheduler;
    private $messages;
    private $subscriptions = [];

    public function __construct(TestScheduler $scheduler, array $messages = [])
    {
        $this->scheduler = $scheduler;
        $this->messages  = $messages;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $this->subscriptions[] = new Subscription($this->scheduler->getClock());
        $index                 = count($this->subscriptions) - 1;

        $currentObservable = $this;
        $disposable        = new CompositeDisposable();
        $scheduler         = $this->scheduler;
        $isDisposed        = false;

        foreach ($this->messages as $message) {
            $notification = $message->getValue();
            $time         = $message->getTime();

            $schedule = function (Notification $innerNotification) use (&$disposable, &$currentObservable, $observer, $scheduler, $time, &$isDisposed) {
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
            $subscriptions[$index] = new Subscription($subscriptions[$index]->getSubscribed(), $scheduler->getClock());
        });

    }

    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
