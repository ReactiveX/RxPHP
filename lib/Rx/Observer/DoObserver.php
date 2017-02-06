<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;

class DoObserver implements ObserverInterface
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
    public function onCompleted()
    {
        call_user_func($this->onCompleted);
    }

    /**
     * @inheritdoc
     */
    public function onError(Exception $error)
    {
        call_user_func_array($this->onError, [$error]);
    }

    /**
     * @inheritdoc
     */
    public function onNext($value)
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