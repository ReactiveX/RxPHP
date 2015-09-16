<?php

namespace Rx\Subject;

use Exception;
use Rx\Disposable\CallbackDisposable;
use Rx\Observer\ScheduledObserver;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

/**
 * Represents an object that is both an observable sequence as well as an observer.
 * Each notification is broadcasted to all subscribed and future observers, subject to buffer trimming policies.
 */
class ReplaySubject extends Subject
{
    private $bufferSize;
    private $windowSize;
    private $queue = [];
    private $maxSafeInt = 9007199254740991;
    private $hasError = false;

    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * ReplaySubject constructor.
     * @param int $bufferSize
     * @param int $windowSize
     * @param SchedulerInterface $scheduler
     */
    public function __construct($bufferSize = null, $windowSize = null, $scheduler = null)
    {
        if ($bufferSize === null || !is_int($bufferSize)) {
            $bufferSize = $this->maxSafeInt;
        }
        if ($bufferSize >= 0) {
            $this->bufferSize = $bufferSize;
        }

        if ($windowSize === null || !is_int($windowSize)) {
            $windowSize = $this->maxSafeInt;
        }
        if ($windowSize >= 0) {
            $this->windowSize = $windowSize;
        }

        if ($scheduler instanceof SchedulerInterface) {
            $this->scheduler = $scheduler;
        }
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $this->assertNotDisposed();

        if (!($scheduler instanceof SchedulerInterface)) {
            $scheduler = $this->scheduler;
        }
        $so = new ScheduledObserver($scheduler, $observer);

        $subscription = $this->createRemovableDisposable($this, $so);

        $this->trim();

        $this->observers[] = $so;

        foreach ($this->queue as $item) {
            $so->onNext($item["value"]);
        }

        if ($this->hasError) {
            $so->onError($this->exception);
        } else {
            if ($this->isStopped) {
                $so->onCompleted();
            }
        }

        $so->ensureActive();

        return $subscription;
    }

    public function onNext($value)
    {
        $now = 0;
        if ($this->scheduler instanceof SchedulerInterface) {
            $now = $this->scheduler->now();
        }
        $this->queue[] = ["interval" => $now, "value" => $value];
        $this->trim();

        $ret = parent::onNext($value);

        /** @var ScheduledObserver $observer */
        foreach ($this->observers as $observer) {
            $observer->ensureActive();
        }

        return $ret;
    }

    public function onCompleted()
    {
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $observers       = $this->observers;
        $this->isStopped = true;

        foreach ($observers as $observer) {
            $observer->onCompleted();
            $observer->ensureActive();
        }

        $this->observers = [];
    }

    public function onError(Exception $exception)
    {
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $observers       = $this->observers;
        $this->isStopped = true;
        $this->exception = $exception;
        $this->hasError  = true;

        $this->trim();

        foreach ($observers as $observer) {
            $observer->onError($exception);
            $observer->ensureActive();
        }

        $this->observers = [];
    }


    private function createRemovableDisposable($subject, $observer)
    {
        return new CallbackDisposable(function () use ($observer, $subject) {
            $observer->dispose();
            if (!$subject->isDisposed()) {
                array_splice($subject->observers, array_search($observer, $subject->observers), 1);
            }
        });
    }

    private function trim()
    {
        if (count($this->queue) > $this->bufferSize) {
            array_shift($this->queue);
        }

        if ($this->scheduler instanceof SchedulerInterface) {
            $now = $this->scheduler->now();
            while (count($this->queue) > 0 && ($now - $this->queue[0]["interval"]) > $this->windowSize) {
                array_shift($this->queue);
            }
        }
    }
}