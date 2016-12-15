<?php

namespace Rx\Observer;

use Exception;
use Rx\ObserverInterface;

class DoObserver implements ObserverInterface
{
    /** @var callable|null */
    private $onNext;

    /** @var callable|null */
    private $onError;

    /** @var callable|null */
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

    public function onCompleted(): void
    {
        call_user_func($this->onCompleted);
    }

    public function onError(Exception $error): void
    {
        call_user_func_array($this->onError, [$error]);
    }

    public function onNext($value): void
    {
        call_user_func_array($this->onNext, [$value]);
    }

    private function getOrDefault(callable $callback = null, $default = null): callable
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}