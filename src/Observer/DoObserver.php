<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\ObserverInterface;

class DoObserver implements ObserverInterface
{
    /**
     * @template T
     * @var callable(T)
     */
    private $onNext;

    /** @var callable */
    private $onError;

    /** @var callable */
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

    public function onCompleted()
    {
        ($this->onCompleted)();
    }

    public function onError(\Throwable $error)
    {
        ($this->onError)($error);
    }

    /**
     * @template T
     * @param T $value
     * @return void
     */
    public function onNext($value)
    {
        ($this->onNext)($value);
    }

    private function getOrDefault(callable $callback = null, callable $default): callable
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}