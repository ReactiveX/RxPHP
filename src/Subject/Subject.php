<?php

declare(strict_types = 1);

namespace Rx\Subject;

use RuntimeException;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\DisposableInterface;
use Rx\ObserverInterface;

class Subject extends Observable implements ObserverInterface, DisposableInterface
{
    protected $exception;
    protected $isDisposed = false;
    protected $isStopped = false;
    protected $observers = [];

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $this->assertNotDisposed();

        if (!$this->isStopped) {
            $this->observers[] = $observer;

            return new InnerSubscriptionDisposable($this, $observer);
        }

        if ($this->exception) {
            $observer->onError($this->exception);

            return new EmptyDisposable();
        }

        $observer->onCompleted();

        return new EmptyDisposable();
    }

    public function isDisposed()
    {
        return $this->isDisposed;
    }

    public function hasObservers(): bool
    {
        return count($this->observers) > 0;
    }

    protected function assertNotDisposed()
    {
        if ($this->isDisposed) {
            throw new RuntimeException('Subject is disposed.');
        }
    }

    public function onCompleted(): void
    {
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $observers       = $this->observers;
        $this->isStopped = true;

        foreach ($observers as $observer) {
            $observer->onCompleted();
        }

        $this->observers = [];
    }

    public function onError(\Throwable $exception): void
    {
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $observers       = $this->observers;
        $this->isStopped = true;
        $this->exception = $exception;

        foreach ($observers as $observer) {
            $observer->onError($exception);
        }

        $this->observers = [];
    }

    public function onNext($value): void
    {
        $this->assertNotDisposed();

        if ($this->isStopped) {
            return;
        }

        $observers = $this->observers;
        foreach ($observers as $observer) {
            $observer->onNext($value);
        }
    }

    public function dispose(): void
    {
        $this->isDisposed = true;
        $this->observers  = [];
    }

    public function removeObserver(ObserverInterface $observer): bool
    {
        $key = array_search($observer, $this->observers, true);

        if (false === $key) {
            return false;
        }

        unset($this->observers[$key]);

        return true;
    }
}
