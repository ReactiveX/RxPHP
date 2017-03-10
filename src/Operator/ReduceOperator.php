<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ReduceOperator implements OperatorInterface
{
    /** @var  callable */
    protected $accumulator;
    protected $seed;
    protected $hasSeed;

    /**
     * @param callable $accumulator
     * @param $seed
     */
    public function __construct(callable $accumulator, $seed)
    {
        $this->accumulator = $accumulator;
        $this->seed        = $seed;
        $this->hasSeed     = null !== $seed;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $hasAccumulation = false;
        $accumulation    = null;
        $hasValue        = false;
        $cbObserver      = new CallbackObserver(
            function ($x) use ($observer, &$hasAccumulation, &$accumulation, &$hasValue) {

                $hasValue = true;

                try {
                    if ($hasAccumulation) {
                        $accumulation = ($this->accumulator)($accumulation, $x);
                    } else {
                        $accumulation    = $this->hasSeed ? ($this->accumulator)($this->seed, $x) : $x;
                        $hasAccumulation = true;
                    }
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            function ($e) use ($observer) {
                $observer->onError($e);
            },
            function () use ($observer, &$hasAccumulation, &$accumulation, &$hasValue) {
                if ($hasValue) {
                    $observer->onNext($accumulation);
                } else {
                    $this->hasSeed && $observer->onNext($this->seed);
                }

                if (!$hasValue && !$this->hasSeed) {
                    $observer->onError(new \Exception('Missing Seed and or Value'));
                }

                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver);

    }
}
