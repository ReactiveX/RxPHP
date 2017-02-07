<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class CombineLatestOperator implements OperatorInterface
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

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
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
                        } catch (\Throwable $e) {
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

            $subscription = $o->subscribe($cbObserver);

            $compositeDisposable->add($subscription);
        }

        return $compositeDisposable;
    }
}
