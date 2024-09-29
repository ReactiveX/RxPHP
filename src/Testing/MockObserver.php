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
    private array $messages = [];

    public function __construct(private readonly TestScheduler $scheduler)
    {
    }

    public function onNext($value): void
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnNextNotification($value)
        );
    }

    public function onError(\Throwable $error): void
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnErrorNotification($error)
        );
    }

    public function onCompleted(): void
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnCompletedNotification()
        );
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
