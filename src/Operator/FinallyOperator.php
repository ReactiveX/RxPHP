<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\BinaryDisposable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

final class FinallyOperator implements OperatorInterface
{
    /** @var callable */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return new BinaryDisposable(
            $observable->subscribe($observer),
            new CallbackDisposable($this->callback)
        );
    }
}