<?php

namespace Rx\Observer;

use Exception;

class CallbackObserver extends AbstractObserver
{
    /** @var callable  */
    private $onNext;

    /** @var callable  */
    private $onError;

    /** @var callable  */
    private $onCompleted;

    /**
     * @param callable|null $onNext
     * @param callable|null $onError
     * @param callable|null $onCompleted
     */
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

    /**
     * @inheritdoc
     */
    protected function completed()
    {
        // Cannot use $foo() here, see: https://bugs.php.net/bug.php?id=47160
        call_user_func($this->onCompleted);
    }

    /**
     * @inheritdoc
     */
    protected function error(Exception $error)
    {
        call_user_func_array($this->onError, [$error]);
    }

    /**
     * @inheritdoc
     */
    protected function next($value)
    {
        call_user_func_array($this->onNext, [$value]);
    }

    /**
     * @param callable|null $callback
     * @param null $default
     * @return callable|null
     */
    private function getOrDefault(callable $callback = null, $default = null)
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}
