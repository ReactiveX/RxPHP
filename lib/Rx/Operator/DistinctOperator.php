<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DistinctOperator implements OperatorInterface
{

    /** @var callable  */
    protected $keySelector;

    /** @var callable  */
    protected $comparer;

    public function __construct(callable $keySelector = null, callable $comparer = null)
    {
        $this->comparer = $comparer;
        $this->keySelector = $keySelector;
    }

    /**
     * @param ObservableInterface $observable
     * @param ObserverInterface $observer
     * @param SchedulerInterface|null $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $values = [];

        $callbackObserver = new CallbackObserver(
            function ($value) use ($observer, &$values) {

                try {
                    $key = $this->keySelector ? call_user_func($this->keySelector, $value) : $value;

                    if ($this->comparer) {
                        foreach ($values as $v) {
                            $comparerEquals = call_user_func($this->comparer, $key, $v);

                            if ($comparerEquals) {
                                return;
                            }
                        }
                        $values[] = $key;
                    } else {
                        if (array_key_exists($key, $values)) {
                            return;
                        }
                        $values[$key] = null;
                    }

                    $observer->onNext($value);

                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']
        );

        return $observable->subscribe($callbackObserver, $scheduler);

    }
}
