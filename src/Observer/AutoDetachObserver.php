<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;
use Rx\DisposableInterface;
use Rx\Disposable\SingleAssignmentDisposable;

class AutoDetachObserver extends AbstractObserver
{
    private $observer;

    private $disposable;

    public function __construct(ObserverInterface $observer)
    {
        $this->observer   = $observer;
        $this->disposable = new SingleAssignmentDisposable();
    }

    public function setDisposable(DisposableInterface $disposable = null)
    {
        $disposable = $disposable ?: new EmptyDisposable();

        $this->disposable->setDisposable($disposable);
    }

    protected function completed()
    {
        try {
            $this->observer->onCompleted();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    protected function error(\Throwable $exception)
    {
        try {
            $this->observer->onError($exception);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    protected function next($value)
    {
        try {
            $this->observer->onNext($value);
        } catch (\Throwable $e) {
            $this->dispose();
            throw $e;
        }
    }

    public function dispose()
    {
        $this->disposable->dispose();
    }
}
