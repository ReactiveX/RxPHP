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

/**
 * @template T
 * @template-extends Observable<T>
 */
class ColdObservable extends Observable
{
    /**
     * @var TestScheduler
     */
    private $scheduler;

    /**
     * @var array<Recorded>
     */
    private $messages;

    /**
     * @var array<Subscription>
     */
    private $subscriptions = [];

    /**
     * @param array<Recorded> $messages
     */
    public function __construct(TestScheduler $scheduler, array $messages = [])
    {
        $this->scheduler = $scheduler;
        $this->messages  = $messages;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $this->subscriptions[] = new Subscription($this->scheduler->getClock());
        $index                 = count($this->subscriptions) - 1;

        $disposable        = new CompositeDisposable();
        $scheduler         = $this->scheduler;
        $isDisposed        = false;

        foreach ($this->messages as $message) {
            $notification = $message->getValue();
            $time         = $message->getTime();

            assert($notification instanceof Notification);

            $schedule = function (Notification $innerNotification) use (&$disposable, $observer, $scheduler, $time, &$isDisposed) {
                $disposable->add($scheduler->scheduleRelativeWithState(null, $time, function () use ($observer, $innerNotification, &$isDisposed) {
                    /** @phpstan-ignore-next-line */
                    if (!$isDisposed) {
                        $innerNotification->accept($observer);
                    }
                    return new EmptyDisposable();
                }));
            };

            $schedule($notification);
        }

        $subscriptions = &$this->subscriptions;

        return new CallbackDisposable(function () use ($index, $scheduler, &$subscriptions, &$isDisposed) {
            $isDisposed            = true;
            $subscriptions[$index] = new Subscription($subscriptions[$index]->getSubscribed(), $scheduler->getClock());
        });

    }

    /**
     * @return array<Subscription>
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }
}
