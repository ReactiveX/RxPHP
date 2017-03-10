<?php

namespace Rx\Testing;

use Exception;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\Notification\OnNextObservableNotification;
use Rx\Observable;
use Rx\ObserverInterface;

/**
 * Mock observer that records all messages.
 */
class MockObserver implements ObserverInterface
{
    /** @var TestScheduler */
    private $scheduler;

    /** @var Recorded[] */
    private $messages = [];

    private $startTime = 0;

    public function __construct($scheduler, $startTime = 0)
    {
        $this->scheduler = $scheduler;
        $this->startTime = $startTime;
    }

    public function onNext($value)
    {
        if ($value instanceof Observable) {
            $notification = new OnNextObservableNotification($value, $this->scheduler);
        } else {
            $notification = new OnNextNotification($value);
        }

        $this->messages[] = new Recorded(
            $this->scheduler->getClock() - $this->startTime,
            $notification
        );
    }

    public function onError(Exception $error)
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock() - $this->startTime,
            new OnErrorNotification($error)
        );
    }

    public function onCompleted()
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock() - $this->startTime,
            new OnCompletedNotification()
        );
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
