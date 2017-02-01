<?php

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\BinaryDisposable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DoFinallyOperator implements OperatorInterface
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
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        return new BinaryDisposable(
            $observable->subscribe($observer, $scheduler),
            new CallbackDisposable($this->callback)
        );
    }
}