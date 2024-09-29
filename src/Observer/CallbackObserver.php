<?php

declare(strict_types = 1);

namespace Rx\Observer;

class CallbackObserver extends AbstractObserver
{

    public function __construct(
        private $onNext = null,
        private $onError = null,
        private $onCompleted = null
    ) {
        $default = function (): void {
        };

        $this->onNext = $this->getOrDefault($onNext, $default);

        $this->onError = $this->getOrDefault($onError, function ($e): void {
            throw $e;
        });

        $this->onCompleted = $this->getOrDefault($onCompleted, $default);
    }

    protected function completed(): void
    {
        // Cannot use $foo() here, see: https://bugs.php.net/bug.php?id=47160
        ($this->onCompleted)();
    }

    protected function error(\Throwable $error): void
    {
        ($this->onError)($error);
    }

    protected function next($value): void
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
