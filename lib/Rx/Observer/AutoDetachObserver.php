<?php

namespace Rx\Observer;

use Exception;
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

    public function setDisposable(DisposableInterface $disposable)
    {
        $this->disposable->setDisposable($disposable);
    }

    protected function completed()
    {
        try {
            $this->observer->onCompleted();
            $this->dispose();
        } catch(Exception $e) {
            $this->dispose(); // todo: should be in finally?
            throw $e;
        }
    }

    protected function error(Exception $exception)
    {
        try {
            $this->observer->onError($exception);
            $this->dispose();
        } catch(Exception $e) {
            $this->dispose(); // todo: should be in finally?
            throw $e;
        }
    }

    protected function next($value)
    {
        try {
            $this->observer->onNext();
        } catch(Exception $e) {
            $this->dispose(); // todo: should be in finally?
            throw $e;
        }
    }

    public function dispose()
    {
        $this->disposable->dispose();
    }
}
