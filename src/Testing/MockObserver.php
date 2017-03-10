<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\ObserverInterface;

/**
 * Mock observer that records all messages.
 */
class MockObserver implements ObserverInterface
{
    private $scheduler;
    private $messages = [];

    public function __construct(TestScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    public function onNext($value)
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnNextNotification($value)
        );
    }

    public function onError(\Throwable $error)
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnErrorNotification($error)
        );
    }

    public function onCompleted()
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnCompletedNotification()
        );
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
