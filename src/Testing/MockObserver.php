<?php

declare(strict_types = 1);

namespace Rx\Testing;

use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\ObserverInterface;

/**
 * @template T
 * Mock observer that records all messages.
 */
class MockObserver implements ObserverInterface
{
    /**
     * @var TestScheduler
     */
    private $scheduler;

    /**
     * @var array<Recorded>
     */
    private $messages = [];

    public function __construct(TestScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @param T $value
     * @return void
     */
    public function onNext($value)
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnNextNotification($value)
        );
    }

    /**
     * @param \Throwable $error
     * @return void
     */
    public function onError(\Throwable $error)
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnErrorNotification($error)
        );
    }

    /**
     * @return void
     */
    public function onCompleted()
    {
        $this->messages[] = new Recorded(
            $this->scheduler->getClock(),
            new OnCompletedNotification()
        );
    }

    /**
     * @return array<Recorded>
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
