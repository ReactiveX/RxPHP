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
    private $startTime = 0;

    public function __construct(TestScheduler $scheduler, int $startTime = 0)
    {
        $this->scheduler = $scheduler;
        $this->startTime = $startTime;
    }

    public function onNext($value)
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock() - $this->startTime,
            new OnNextNotification($value)
        );
    }

    public function onError(\Throwable $error)
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
