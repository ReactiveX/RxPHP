<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final readonly class ConcatOperator implements OperatorInterface
{
    public function __construct(private ObservableInterface $subsequentObservable)
    {
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disp = new SerialDisposable();

        $cbObserver = new CallbackObserver(
            fn ($x) => $observer->onNext($x),
            fn ($err) => $observer->onError($err),
            function () use ($observer, $disp): void {
                $disp->setDisposable($this->subsequentObservable->subscribe($observer));
            }
        );

        $disp->setDisposable($observable->subscribe($cbObserver));

        return $disp;
    }
}
