<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class MinOperator implements OperatorInterface
{
    /** @var callable|null */
    private $comparer;

    /**
     * MinOperator constructor.
     * @param $comparer callable
     */
    public function __construct(callable $comparer = null)
    {
        if ($comparer === null) {
            $comparer = function ($x, $y) {
                return $x > $y ? 1 : ($x < $y ? -1 : 0);
            };
        }

        $this->comparer = $comparer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        $previousMin = null;
        $comparing   = false;

        return $observable->subscribe(new CallbackObserver(
            function ($x) use (&$comparing, &$previousMin, $observer) {
                if (!$comparing) {
                    $comparing = true;
                    $previousMin = $x;

                    return;
                }

                try {
                    $result = call_user_func($this->comparer, $x, $previousMin);
                    if ($result < 0) {
                        $previousMin = $x;
                    }
                } catch (\Exception $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use (&$comparing, &$previousMin, $observer) {
                if ($comparing) {
                    $observer->onNext($previousMin);
                    $observer->onCompleted();
                    return;
                }

                $observer->onError(new \Exception("Empty"));
            }
            ), $scheduler);
    }
}
