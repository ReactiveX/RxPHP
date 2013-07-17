<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;

class CallbackObserver extends AbstractObserver
{
    private $onNext;
    private $onError;
    private $onCompleted;

    public function __construct($onNext = null, $onError = null, $onCompleted = null)
    {
        $this->onNext      = $this->getOrDefault($onNext, function(){});
        $this->onError     = $this->getOrDefault($onError, function($e){ throw $e; });
        $this->onCompleted = $this->getOrDefault($onCompleted, function(){});
    }

    protected function completed()
    {
        $onCompleted= $this->onCompleted;
        $onCompleted();
    }

    protected function error(Exception $error)
    {
        $onError = $this->onError;
        $onError($error);
    }

    protected function next($value)
    {
        $onNext = $this->onNext;
        $onNext($value);
    }

    private function getOrDefault($callback, $default)
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}
