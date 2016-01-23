<?php

namespace Rx\Observer;

use Exception;

class CallbackObserver extends AbstractObserver
{
    /** @var callable|null  */
    private $onNext;

    /** @var callable|null  */
    private $onError;

    /** @var callable|null  */
    private $onCompleted;

    public function __construct(callable $onNext = null, callable $onError = null, callable $onCompleted = null)
    {
        $default = function () {
        };

        $this->onNext = $this->getOrDefault($onNext, $default);

        $this->onError = $this->getOrDefault($onError, function ($e) {
            throw $e;
        });

        $this->onCompleted = $this->getOrDefault($onCompleted, $default);
    }

    protected function completed()
    {
        // Cannot use $foo() here, see: https://bugs.php.net/bug.php?id=47160
        call_user_func($this->onCompleted);
    }

    protected function error(Exception $error)
    {
        call_user_func_array($this->onError, [$error]);
    }

    protected function next($value)
    {
        call_user_func_array($this->onNext, [$value]);
    }

    private function getOrDefault(callable $callback = null, $default = null)
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}
