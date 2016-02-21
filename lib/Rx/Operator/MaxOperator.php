<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class MaxOperator implements OperatorInterface
{
    /** @var callable|null */
    private $comparer;

    /**
     * MaxOperator constructor.
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
        $previousMax = null;
        $comparing   = false;

        return $observable->subscribe(new CallbackObserver(
            function ($x) use (&$comparing, &$previousMax, $observer) {
                if (!$comparing) {
                    $comparing = true;
                    $previousMax = $x;

                    return;
                }

                try {
                    $result = call_user_func($this->comparer, $x, $previousMax);
                    if ($result > 0) {
                        $previousMax = $x;
                    }
                } catch (\Exception $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use (&$comparing, &$previousMax, $observer) {
                if ($comparing) {
                    $observer->onNext($previousMax);
                    $observer->onCompleted();
                    return;
                }

                $observer->onError(new \Exception("Empty"));
            }
        ), $scheduler);
    }
}
