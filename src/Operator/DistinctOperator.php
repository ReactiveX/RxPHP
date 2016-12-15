<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DistinctOperator implements OperatorInterface
{

    /** @var callable */
    protected $keySelector;

    /** @var callable */
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
        $values = [];

        $callbackObserver = new CallbackObserver(
            function ($value) use ($observer, &$values) {

                try {
                    $key = $this->keySelector ? call_user_func($this->keySelector, $value) : $value;

                    foreach ($values as $v) {
                        $comparerEquals = call_user_func($this->comparer, $key, $v);

                        if ($comparerEquals) {
                            return;
                        }
                    }

                    $values[] = $key;
                    $observer->onNext($value);

                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($callbackObserver);
    }
}
