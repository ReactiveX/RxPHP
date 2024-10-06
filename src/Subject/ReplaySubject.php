<?php

declare(strict_types = 1);

namespace Rx\Subject;

use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Observer\ScheduledObserver;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

/**
 * Represents an object that is both an observable sequence as well as an observer.
 * Each notification is broadcasted to all subscribed and future observers, subject to buffer trimming policies.
 */
class ReplaySubject extends Subject
{
    private ?int $bufferSize;

    private ?int $windowSize;

    private array $queue = [];

    private int $maxSafeInt = PHP_INT_MAX;

    private bool $hasError = false;

    private \Rx\SchedulerInterface $scheduler;

    public function __construct(int $bufferSize = null, int $windowSize = null, SchedulerInterface $scheduler = null)
    {
        $bufferSize = $bufferSize ?? $this->maxSafeInt;

        if ($bufferSize >= 0) {
            $this->bufferSize = $bufferSize;
        }

        $windowSize = $windowSize ?? $this->maxSafeInt;

        if ($windowSize >= 0) {
            $this->windowSize = $windowSize;
        }

        $this->scheduler = $scheduler ?: Scheduler::getDefault();
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $this->assertNotDisposed();

        $so = new ScheduledObserver($this->scheduler, $observer);

        $subscription = $this->createRemovableDisposable($this, $so);

        $this->trim();

        $this->observers[] = $so;

        foreach ($this->queue as $item) {
            $so->onNext($item['value']);
        }

        if ($this->hasError) {
            $so->onError($this->exception);
        } elseif ($this->isStopped) {
            $so->onCompleted();
        }

        $so->ensureActive();

        return $subscription;
    }

    public function onNext($value): void
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

    public function onCompleted(): void
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

    public function onError(\Throwable $exception): void
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

    private function createRemovableDisposable(self $subject, \Rx\Observer\ScheduledObserver $observer): DisposableInterface
    {
        return new CallbackDisposable(function () use ($observer, $subject): void {
            $observer->dispose();
            if (!$subject->isDisposed()) {
                array_splice($subject->observers, (int)array_search($observer, $subject->observers, true), 1);
            }
        });
    }

    private function trim(): void
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
