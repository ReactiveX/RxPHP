<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;

class CallbackObserver implements ObserverInterface
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

    public function onCompleted()
    {
        $onCompleted= $this->onCompleted;
        $onCompleted();
    }

    public function onError(Exception $error)
    {
        $onError = $this->onError;
        $onError($error);
    }

    public function onNext($value)
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
