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

    function __construct($accumulator, $seed)
    {
        if (!is_callable($accumulator)) {
            throw new \InvalidArgumentException('Accumulator should be a callable.');
        }

        $this->accumulator = $accumulator;
        $this->seed        = $seed;
        $this->hasSeed     = $seed ? true : false;
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

        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, &$hasAccumulation, &$accumulation, &$hasValue) {

                !$hasValue && ($hasValue = true);

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
                $hasValue && $observer->onNext($accumulation);
                !$hasValue && $this->hasSeed && $observer->onNext($this->seed);
                !$hasValue && !$this->hasSeed && $observer->onError(new \Exception("Missing Seed and or Value"));
                $observer->onCompleted();
            })
        );

    }
}