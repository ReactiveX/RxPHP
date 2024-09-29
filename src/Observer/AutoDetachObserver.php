<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;
use Rx\DisposableInterface;
use Rx\Disposable\SingleAssignmentDisposable;

class AutoDetachObserver extends AbstractObserver
{
    private SingleAssignmentDisposable $disposable;

    public function __construct(private readonly ObserverInterface $observer)
    {
        $this->disposable = new SingleAssignmentDisposable();
    }

    public function setDisposable(DisposableInterface $disposable = null): void
    {
        $disposable = $disposable ?: new EmptyDisposable();

        $this->disposable->setDisposable($disposable);
    }

    protected function completed(): void
    {
        try {
            $this->observer->onCompleted();
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    protected function error(\Throwable $exception): void
    {
        try {
            $this->observer->onError($exception);
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    protected function next($value): void
    {
        try {
            $this->observer->onNext($value);
        } catch (\Throwable $e) {
            $this->dispose();
            throw $e;
        }
    }

    public function dispose(): void
    {
        $this->disposable->dispose();
    }
}
