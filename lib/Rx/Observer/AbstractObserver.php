<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;
use Rx\Disposable\SingleAssignmentDisposable;

abstract class AbstractObserver implements ObserverInterface
{
    private $isStopped = false;

    function onCompleted()
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->completed();
    }

    function onError(Exception $error)
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->error($error);
    }

    function onNext($value)
    {
        if ($this->isStopped) {
            return;
        }

        $this->next($value);
    }

    abstract protected function completed();
    abstract protected function next($value);
    abstract protected function error(Exception $error);
}
