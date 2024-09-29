<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ScanOperator implements OperatorInterface
{
    public function __construct(
        private readonly null|\Closure $accumulator,
        private $seed = null
    ) {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $hasValue        = false;
        $hasAccumulation = false;
        $accumulation    = $this->seed;
        $hasSeed         = $this->seed !== null;
        $cbObserver      = new CallbackObserver(
            function ($x) use ($observer, &$hasAccumulation, &$accumulation, &$hasSeed, &$hasValue): void {
                $hasValue = true;
                if ($hasAccumulation) {
                    $accumulation = ($this->tryCatch($this->accumulator))($accumulation, $x);
                } else {
                    $accumulation    = $hasSeed ? ($this->tryCatch($this->accumulator))($this->seed, $x) : $x;
                    $hasAccumulation = true;
                }
                if ($accumulation instanceof \Throwable) {
                    $observer->onError($accumulation);
                    return;
                }
                $observer->onNext($accumulation);
            },
            fn ($err) => $observer->onError($err),
            function () use ($observer, &$hasValue, &$hasSeed): void {
                if (!$hasValue && $hasSeed) {
                    $observer->onNext($this->seed);
                }
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver);
    }

    private function tryCatch($functionToWrap)
    {
        return function ($x, $y) use ($functionToWrap) {
            try {
                return $functionToWrap($x, $y);
            } catch (\Throwable $e) {
                return $e;
            }
        };
    }
}
