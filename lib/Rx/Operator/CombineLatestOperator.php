<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class CombineLatestOperator implements OperatorInterface
{
    /** @var ObservableInterface[] */
    private $observables;

    /** @var callable */
    private $resultSelector;

    public function __construct(array $observables, callable $resultSelector = null)
    {
        if (null === $resultSelector) {
            $resultSelector = function () {
                return func_get_args();
            };
        }

        foreach ($observables as $observable) {
            if (!$observable instanceof ObservableInterface) {
                throw new \InvalidArgumentException;
            }
        }

        $this->observables    = $observables;
        $this->resultSelector = $resultSelector;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        array_unshift($this->observables, $observable);

        $observables         = &$this->observables;
        $compositeDisposable = new CompositeDisposable();
        $hasValue            = [];
        $values              = array_keys($observables);
        $count               = count($observables);
        $waitingToComplete   = $count;
        $waitingForValues    = $count;

        foreach ($observables as $key => $o) {

            $hasValue[$key] = false;

            $cbObserver = new CallbackObserver(
                function ($value) use ($count, &$hasValue, $key, &$values, $observer, &$waitingForValues, &$waitingToComplete) {

                    // If an observable has completed before it has emitted, we need to complete right away
                    if ($waitingForValues > $waitingToComplete) {
                        $observer->onCompleted();
                        return;
                    }

                    if ($waitingForValues > 0 && !$hasValue[$key]) {
                        $hasValue[$key] = true;
                        $waitingForValues--;
                    }

                    $values[$key] = $value;
                    if ($waitingForValues === 0) {
                        try {
                            $result = call_user_func_array($this->resultSelector, $values);
                            $observer->onNext($result);
                        } catch (\Exception $e) {
                            $observer->onError($e);
                        }
                    }
                },
                [$observer, 'onError'],
                function () use (&$waitingToComplete, $observer) {
                    $waitingToComplete--;
                    if ($waitingToComplete === 0) {
                        $observer->onCompleted();
                    }
                }
            );

            $subscription = $o->subscribe($cbObserver, $scheduler);

            $compositeDisposable->add($subscription);
        }

        return $compositeDisposable;
    }
}
