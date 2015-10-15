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
     * @param \Closure $accumulator
     * @param $seed
     */
    public function __construct(\Closure $accumulator, $seed = null)
    {
        $this->accumulator = $accumulator;
        $this->seed = $seed;
        $this->hasValue = false;
        $this->hasAccumulation = false;
        $this->accumulation = $seed;
        $this->hasSeed = $seed !== null;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer) {
                $this->hasValue = true;
                if ($this->hasAccumulation) {
                    $this->accumulation = call_user_func($this->tryCatch($this->accumulator), $this->accumulation, $x);
                } else {
                    $this->accumulation = $this->hasSeed ? call_user_func($this->tryCatch($this->accumulator), $this->seed, $x) : $x;
                    $this->hasAccumulation = true;
                }
                if ($this->accumulation instanceof \Exception) {
                    $observer->onError($this->accumulation);
                    return;
                }
                $observer->onNext($this->accumulation);
            },
            function ($e) use ($observer) {
                $observer->onError($e);
            },
            function () use ($observer) {
                if (!$this->hasValue && $this->hasSeed) {
                    $observer->onNext($this->seed);
                }
                $observer->onCompleted();
            }
        ));
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