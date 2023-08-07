<?php

declare(strict_types = 1);

namespace Rx\Subject;

use RuntimeException;
use Rx\Disposable\EmptyDisposable;
use Rx\Observable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

/**
 * @template T
 * @template-extends Observable<T>
 * @template-implements ObservableInterface<T>
 */
class Subject extends Observable implements ObserverInterface, DisposableInterface, ObservableInterface
{
    /**
     * @var ?\Throwable
     */
    protected $exception;

    /**
     * @var bool
     */
    protected $isDisposed = false;

    /**
     * @var bool
     */
    protected $isStopped = false;

    /**
     * @var array<ObserverInterface>
     */
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

    /**
     * @return bool
     */
    public function isDisposed()
    {
        return $this->isDisposed;
    }

    /**
     * @return bool
     */
    public function hasObservers()
    {
        return count($this->observers) > 0;
    }

    /**
     * @return void
     */
    protected function assertNotDisposed()
    {
        if ($this->isDisposed) {
            throw new RuntimeException('Subject is disposed.');
        }
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function onError(\Throwable $exception)
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

    /**
     * @param T $value
     * @return void
     */
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

    /**
     * @return void
     */
    public function dispose()
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
