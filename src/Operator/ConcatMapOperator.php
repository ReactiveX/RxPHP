<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

final class ConcatMapOperator implements OperatorInterface
{
    public function __construct(
        private $selector,
        private $resultSelector = null
    ) {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->mapWithIndex(function (int $index, $value) use ($observable, $observer) {

            try {
                $result = ($this->selector)($value, $index, $observable);

                if ($this->resultSelector) {
                    return $result->mapWithIndex(function ($innerIndex, $innerValue) use ($value, $index) {
                        return ($this->resultSelector)($value, $innerValue, $index, $innerIndex);
                    });
                }

                return $result;

            } catch (\Throwable $e) {
                $observer->onError($e);
            }
        })
            ->concatAll()
            ->subscribe($observer);
    }
}
