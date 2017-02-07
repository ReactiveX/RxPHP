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
    /** @var int */
    private $bufferSize;

    /** @var int */
    private $windowSize;

    /** @var array */
    private $queue = [];

    /** @var int */
    private $maxSafeInt = PHP_INT_MAX;

    /** @var bool */
    private $hasError = false;

    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * ReplaySubject constructor.
     * @param int $bufferSize
     * @param int $windowSize
     * @param SchedulerInterface $scheduler
     */
    public function __construct($bufferSize = null, $windowSize = null, SchedulerInterface $scheduler = null)
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

        if (!$scheduler) {
            $scheduler = new ImmediateScheduler();
        }
        $this->scheduler = $scheduler;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $this->assertNotDisposed();

        if (!$scheduler) {
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
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $now = $this->scheduler->now();

        $this->queue[] = ["interval" => $now, "value" => $value];
        $this->trim();

        /** @var ScheduledObserver $observer */
        foreach ($this->observers as $observer) {
            $observer->onNext($value);
            $observer->ensureActive();
        }

    }

    public function onCompleted()
    {
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;

        /** @var ScheduledObserver $observer */
        foreach ($this->observers as $observer) {
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

        $this->isStopped = true;
        $this->exception = $exception;
        $this->hasError  = true;

        $this->trim();

        /** @var ScheduledObserver $observer */
        foreach ($this->observers as $observer) {
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
                array_splice($subject->observers, array_search($observer, $subject->observers, true), 1);
            }
        });
    }

    private function trim()
    {
        if (count($this->queue) > $this->bufferSize) {
            array_shift($this->queue);
        }

        if (null !== $this->scheduler) {
            $now = $this->scheduler->now();
            while (count($this->queue) > 0 && ($now - $this->queue[0]["interval"]) > $this->windowSize) {
                array_shift($this->queue);
            }
        }
    }
}
