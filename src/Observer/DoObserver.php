<?php

declare(strict_types = 1);

namespace Rx\Observer;

use Rx\ObserverInterface;

class DoObserver implements ObserverInterface
{
    public function __construct(
        private null|\Closure $onNext = null,
        private null|\Closure $onError = null,
        private null|\Closure $onCompleted = null
    ) {
        $default = function (): void {
        };

        $this->onNext = $this->getOrDefault($onNext, $default);

        $this->onError = $this->getOrDefault($onError, function ($e): void {
            throw $e;
        });

        $this->onCompleted = $this->getOrDefault($onCompleted, $default);
    }

    public function onCompleted(): void
    {
        ($this->onCompleted)();
    }

    public function onError(\Throwable $error): void
    {
        ($this->onError)($error);
    }

    public function onNext($value): void
    {
        ($this->onNext)($value);
    }

    private function getOrDefault(callable $callback = null, $default = null): callable
    {
        if (null === $callback) {
            return $default;
        }

        return $callback;
    }
}