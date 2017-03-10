<?php

namespace Rx\Notification;

use Rx\Observable;
use Rx\Testing\MockHigherOrderObserver;
use Rx\Testing\TestScheduler;

class OnNextObservableNotification extends OnNextNotification
{
    /** @var MockHigherOrderObserver */
    private $observer;

    public function __construct($value, TestScheduler $scheduler = null)
    {
        parent::__construct($value);
        if (!$scheduler) {
            $scheduler = new TestScheduler();
        }

        $this->observer = new MockHigherOrderObserver($scheduler, $scheduler->getClock());

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