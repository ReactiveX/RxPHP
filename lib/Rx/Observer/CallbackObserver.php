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
        $this->onNext      = $this->getOrDefault($onNext);
        $this->onError     = $this->getOrDefault($onError);
        $this->onCompleted = $this->getOrDefault($onCompleted);
    }

    public function onCompleted()
    {
        $this->onCompleted->__invoke();
    }

    public function onError(Exception $error)
    {
        $this->onError->__invoke($error);
    }

    public function onNext($value)
    {
        $this->onNext->__invoke($value);
    }

    private function getOrDefault($callback)
    {
        if (null === $callback) {
            return function() {};
        }

        return $callback;
    }
}
