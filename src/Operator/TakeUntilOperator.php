<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class TakeUntilOperator implements OperatorInterface
{
    public function __construct(private ObservableInterface $other)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {

        return new CompositeDisposable([
            $this->other->subscribe(
                new CallbackObserver(
                    fn () => $observer->onCompleted(),
                    [$observer, 'onError']
                )
            ),
            $observable->subscribe($observer)
        ]);
    }
}
