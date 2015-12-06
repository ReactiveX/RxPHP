<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class MapOperator implements OperatorInterface
{
    /** @var callable  */
    private $selector;

    /**
     * MapOperator constructor.
     * @param $selector
     */
    public function __construct(callable $selector)
    {
        $this->selector = $selector;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $selectObserver = new CallbackObserver(
          function($nextValue) use ($observer) {
              $value = null;
              try {
                  $value = call_user_func($this->selector, $nextValue);
              } catch (\Exception $e) {
                  $observer->onError($e);
              }
              $observer->onNext($value);
          },
          function($error) use ($observer) {
              $observer->onError($error);
          },
          function() use ($observer) {
              $observer->onCompleted();
          }
        );

        return $observable->subscribe($selectObserver, $scheduler);
    }
}