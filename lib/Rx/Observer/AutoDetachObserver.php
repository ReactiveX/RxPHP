<?php

namespace Rx\Observer;

use Exception;
use Rx\Disposable\EmptyDisposable;
use Rx\ObserverInterface;
use Rx\DisposableInterface;
use Rx\Disposable\SingleAssignmentDisposable;

class AutoDetachObserver extends AbstractObserver
{
    private $observer;

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
        } catch (Exception $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    protected function error(Exception $exception)
    {
        try {
            $this->observer->onError($exception);
        } catch (Exception $e) {
            throw $e;
        } finally {
            $this->dispose();
        }
    }

    protected function next($value)
    {
        try {
            $this->observer->onNext($value);
        } catch (Exception $e) {
            $this->dispose();
            throw $e;
        }
    }

    public function dispose()
    {
        $this->disposable->dispose();
    }
}
