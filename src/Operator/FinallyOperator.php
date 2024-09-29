<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\BinaryDisposable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

final class FinallyOperator implements OperatorInterface
{
    public function __construct(private readonly null|\Closure $callback)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return new BinaryDisposable(
            $observable->subscribe($observer),
            new CallbackDisposable($this->callback)
        );
    }
}