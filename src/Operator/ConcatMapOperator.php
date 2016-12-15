<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

class ConcatMapOperator implements OperatorInterface
{
    /** @var int */
    private $count;

    /** @var callable */
    private $selector;

    /** @var callable */
    private $resultSelector;

    public function __construct(callable $selector, callable $resultSelector = null)
    {
        $this->selector       = $selector;
        $this->count          = 0;
        $this->resultSelector = $resultSelector;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->mapWithIndex(function (int $index, $value) use ($observable, $observer) {

            try {
                $result = call_user_func_array($this->selector, [$value, $index, $observable]);

                if (!$result instanceof Observable) {
                    throw new \Exception('concatMap Error:  You must return an Observable from the concatMap selector');
                }

                if ($this->resultSelector) {
                    return $result->mapWithIndex(function ($innerIndex, $innerValue) use ($value, $index) {
                        return call_user_func_array($this->resultSelector, [$value, $innerValue, $index, $innerIndex]);
                    });
                }

                return $result;

            } catch (\Exception $e) {
                $observer->onError($e);
            }
        })
            ->concatAll()
            ->subscribe($observer);
    }
}
