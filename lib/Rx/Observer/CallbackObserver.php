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
        // Cannot use $foo() here, see: https://bugs.php.net/bug.php?id=47160
        call_user_func($this->onCompleted);
    }

    protected function error(Exception $error)
    {
        call_user_func_array($this->onError, array($error));
    }

    protected function next($value)
    {
        call_user_func_array($this->onNext, array($value));
    }

    private function getOrDefault($callback, $default)
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}
