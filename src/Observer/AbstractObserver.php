<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;

abstract class AbstractObserver implements ObserverInterface
{
    private $isStopped = false;

    public function onCompleted()
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->completed();
    }

    public function onError(Exception $error)
    {
        if ($this->isStopped) {
            return;
        }

        $this->isStopped = true;
        $this->error($error);
    }

    public function onNext($value)
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
