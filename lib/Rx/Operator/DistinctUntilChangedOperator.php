<?php


namespace Rx\Operator;


use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DistinctUntilChangedOperator implements OperatorInterface
{

    protected $keySelector;

    protected $comparer;

    function __construct($keySelector = null, $comparer = null)
    {

        $this->comparer = $comparer ?: function ($x, $y) {
            return $x == $y;
        };

        $this->keySelector = $keySelector;

    }


    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {

        $hasCurrentKey = false;
        $currentKey    = null;

        return $observable->subscribe(new CallbackObserver(
          function ($value) use ($observer, &$hasCurrentKey, &$currentKey) {
              $key = $value;
              if ($this->keySelector) {
                  try {
                      $key = call_user_func($this->keySelector, $value);
                  } catch (\Exception $e) {
                      return $observer->onError($e);
                  }
              }

              $comparerEquals = null;
              if ($hasCurrentKey) {
                  try {
                      $comparerEquals = call_user_func($this->comparer, $currentKey, $key);
                  } catch (\Exception $e) {
                      return $observer->onError($e);
                  }
              }

              if (!$hasCurrentKey || !$comparerEquals) {
                  $hasCurrentKey = true;
                  $currentKey    = $key;
                  $observer->onNext($value);
              }

          },
          function ($e) use ($observer) {
              $observer->onError($e);
          },
          function () use ($observer) {
              $observer->onCompleted();
          }));

    }

}