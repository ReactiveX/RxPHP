<?php

namespace Rx\Notification;

use Rx\Observable;
use Rx\SchedulerInterface;
use Rx\Testing\MockObserver;
use Rx\Testing\TestScheduler;

class OnNextObservableNotification extends OnNextNotification
{
    /** @var MockObserver */
    private $observer;

    public function __construct($value, TestScheduler $scheduler = null, $startTime = 0)
    {
        parent::__construct($value);
        if (!$scheduler) {
            $scheduler = new TestScheduler();
        }

        $this->observer = new MockObserver($scheduler, $startTime);

        /** @var Observable $value */
        $value->subscribe($this->observer, $scheduler);

        $scheduler->start();
    }

    public function equals($other)
    {
        $messages1 = $this->getMessages();
        /** @var OnNextObservableNotification $other */
        $messages2 = $other->getMessages();

        if (count($messages1) != count($messages2)) {
            return false;
        }

        for ($i = 0; $i < count($messages1); $i++) {
            if (!$messages1[$i]->equals($messages2[$i])) {
                return false;
            }
        }

        return true;
    }

    public function getMessages()
    {
        return $this->observer->getMessages();
    }

    public function __toString()
    {
        return '[' . join(', ', $this->getMessages()) . ']';
    }
}