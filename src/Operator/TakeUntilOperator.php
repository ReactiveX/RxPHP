<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

class TakeUntilOperator implements OperatorInterface
{
    private $other;

    public function __construct(ObservableInterface $other)
    {
        $this->other = $other;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {

        return new CompositeDisposable([
            $observable->subscribe($observer),
            $this->other->subscribe(
                new CallbackObserver(
                    [$observer, 'onCompleted'],
                    [$observer, 'onError']
                )
            )
        ]);
    }
}
