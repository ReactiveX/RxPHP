<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ScanOperator implements OperatorInterface
{
    /** @var  \Closure */
    private $accumulator;

    /** @var  mixed */
    private $seed;

    /**
     * ScanOperator constructor.
     * @param callable $accumulator
     * @param $seed
     */
    public function __construct(callable $accumulator, $seed = null)
    {
        $this->accumulator = $accumulator;
        $this->seed        = $seed;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $hasValue        = false;
        $hasAccumulation = false;
        $accumulation    = $this->seed;
        $hasSeed         = $this->seed !== null;
        $cbObserver      = new CallbackObserver(
            function ($x) use ($observer, &$hasAccumulation, &$accumulation, &$hasSeed, &$hasValue) {
                $hasValue = true;
                if ($hasAccumulation) {
                    $accumulation = call_user_func($this->tryCatch($this->accumulator), $accumulation, $x);
                } else {
                    $accumulation    = $hasSeed ? call_user_func($this->tryCatch($this->accumulator), $this->seed, $x) : $x;
                    $hasAccumulation = true;
                }
                if ($accumulation instanceof \Exception) {
                    $observer->onError($accumulation);
                    return;
                }
                $observer->onNext($accumulation);
            },
            [$observer, 'onError'],
            function () use ($observer, &$hasValue, &$hasSeed) {
                if (!$hasValue && $hasSeed) {
                    $observer->onNext($this->seed);
                }
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver, $scheduler);
    }

    private function tryCatch($functionToWrap)
    {
        return function ($x, $y) use ($functionToWrap) {
            try {
                return call_user_func($functionToWrap, $x, $y);
            } catch (\Exception $e) {
                return $e;
            }
        };
    }
}
