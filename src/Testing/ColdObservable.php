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
    public function __construct(
        private readonly TestScheduler $scheduler,
        private readonly array         $messages = [],
        private array                  $subscriptions = []
    ) {
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

            $schedule = function (Notification $innerNotification) use (&$disposable, &$currentObservable, $observer, $scheduler, $time, &$isDisposed): void {
                $disposable->add($scheduler->scheduleRelativeWithState(null, $time, function () use ($observer, $innerNotification, &$isDisposed): \Rx\Disposable\EmptyDisposable {
                    $innerNotification->accept($observer);
                    return new EmptyDisposable();
                }));
            };

            $schedule($notification);
        }

        $subscriptions = &$this->subscriptions;

        return new CallbackDisposable(function () use (&$currentObservable, $index, $observer, $scheduler, &$subscriptions, &$isDisposed): void {
            $isDisposed            = true;
            $subscriptions[$index] = new Subscription($subscriptions[$index]->getSubscribed(), $scheduler->getClock());
        });

    }

    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
