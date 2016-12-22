<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Observer\CallbackObserver;

final class DoOnEachOperator implements OperatorInterface
{
    private $onEachObserver;

    public function __construct(ObserverInterface $observer)
    {
        $this->onEachObserver = $observer;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $cbObserver = new CallbackObserver(
            function ($x) use ($observer) {
                try {
                    $this->onEachObserver->onNext($x);
                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
                $observer->onNext($x);

            },
            function ($err) use ($observer) {
                try {
                    $this->onEachObserver->onError($err);
                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
                $observer->onError($err);
            },
            function () use ($observer) {
                try {
                    $this->onEachObserver->onCompleted();
                } catch (\Exception $e) {
                    return $observer->onError($e);
                }
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($cbObserver);
    }
}
