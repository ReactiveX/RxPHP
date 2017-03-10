<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class DistinctUntilChangedOperator implements OperatorInterface
{
    protected $keySelector;

    protected $comparer;

    public function __construct(callable $keySelector = null, callable $comparer = null)
    {
        $this->comparer = $comparer ?: function ($x, $y) {
            return $x == $y;
        };

        $this->keySelector = $keySelector;
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
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($cbObserver);

    }
}
