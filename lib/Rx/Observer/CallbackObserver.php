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

    private function getOrDefault($callback)
    {
        if (null === $callback) {
            return function() {};
        }

        return $callback;
    }
}
