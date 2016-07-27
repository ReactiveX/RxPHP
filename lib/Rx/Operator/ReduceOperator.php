<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ReduceOperator implements OperatorInterface
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
        $this->hasSeed     = !is_null($seed);
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $hasAccumulation = false;
        $accumulation    = null;
        $hasValue        = false;
        $cbObserver      = new CallbackObserver(
            function ($x) use ($observer, &$hasAccumulation, &$accumulation, &$hasValue) {

                $hasValue = true;

                try {
                    if ($hasAccumulation) {
                        $accumulation = call_user_func($this->accumulator, $accumulation, $x);
                    } else {
                        $accumulation    = $this->hasSeed ? call_user_func($this->accumulator, $this->seed, $x) : $x;
                        $hasAccumulation = true;
                    }
                } catch (\Exception $e) {
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
                    $observer->onError(new \Exception("Missing Seed and or Value"));
                }

                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver, $scheduler);

    }
}
