<?php

namespace Rx\Subject;

use Exception;
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

    public function subscribe(ObserverInterface $observer, $scheduler = null)
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

    public function hasObservers()
    {
        return count($this->observers) > 0;
    }

    protected function assertNotDisposed()
    {
        if ($this->isDisposed) {
            throw new RuntimeException('Subject is disposed.');
        }
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

        foreach ($observers as $observer) {
            $observer->onError($exception);
        }

        $this->observers = [];
    }

    public function onNext($value)
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

    public function dispose()
    {
        $this->isDisposed = true;
        $this->observers  = [];
    }

    public function removeObserver(ObserverInterface $observer)
    {
        $key = array_search($observer, $this->observers, true);

        if (false === $key) {
            return false;
        }

        unset($this->observers[$key]);

        return true;
    }
}
