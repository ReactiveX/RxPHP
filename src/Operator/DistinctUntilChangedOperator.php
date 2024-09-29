<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class DistinctUntilChangedOperator implements OperatorInterface
{
    public function __construct(
        private readonly null|\Closure $keySelector = null,
        protected null|\Closure        $comparer = null
    ) {
        $this->comparer = $comparer ?: function ($x, $y): bool {
            return $x == $y;
        };
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $hasCurrentKey = false;
        $currentKey    = null;
        $cbObserver    = new CallbackObserver(
            function ($value) use ($observer, &$hasCurrentKey, &$currentKey) {
                $key = $value;
                if ($this->keySelector) {
                    try {
                        $key = ($this->keySelector)($value);
                    } catch (\Throwable $e) {
                        return $observer->onError($e);
                    }
                }

                $comparerEquals = null;
                if ($hasCurrentKey) {
                    try {
                        $comparerEquals = ($this->comparer)($currentKey, $key);
                    } catch (\Throwable $e) {
                        return $observer->onError($e);
                    }
                }

                if (!$hasCurrentKey || !$comparerEquals) {
                    $hasCurrentKey = true;
                    $currentKey    = $key;
                    $observer->onNext($value);
                }

            },
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()
        );

        return $observable->subscribe($cbObserver);

    }
}
