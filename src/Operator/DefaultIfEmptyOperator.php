<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class DefaultIfEmptyOperator implements OperatorInterface
{
    /** @var  ObservableInterface */
    private $observable;

    /** @var bool */
    private $passThrough = false;

    public function __construct(ObservableInterface $observable)
    {
        $this->observable = $observable;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposable = new SerialDisposable();
        $cbObserver = new CallbackObserver(
            function ($x) use ($observer) {
                $this->passThrough = true;
                $observer->onNext($x);
            },
            [$observer, 'onError'],
            function () use ($observer, $disposable) {
                if (!$this->passThrough) {
                    $disposable->setDisposable($this->observable->subscribe($observer));
                    return;
                }

                $observer->onCompleted();
            }
        );

        $subscription = $observable->subscribe($cbObserver);

        $disposable->setDisposable($subscription);

        return $disposable;
    }
}
