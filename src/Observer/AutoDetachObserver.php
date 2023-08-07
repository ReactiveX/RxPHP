<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;
use Rx\DisposableInterface;
use Rx\Disposable\SingleAssignmentDisposable;

class AutoDetachObserver extends AbstractObserver
{
    /**
     * @var ObserverInterface
     */
    private $observer;

    /**
     * @var SingleAssignmentDisposable
     */
    private $disposable;

    public function __construct(ObserverInterface $observer)
    {
        $this->observer   = $observer;
        $this->disposable = new SingleAssignmentDisposable();
    }

    /**
     * @return void
     */
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

    /**
     * @template T
     * @param T $value
     * @return void
     */
    protected function next($value)
    {
        try {
            $this->observer->onNext($value);
        } catch (\Throwable $e) {
            $this->dispose();
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function dispose()
    {
        $this->disposable->dispose();
    }
}
