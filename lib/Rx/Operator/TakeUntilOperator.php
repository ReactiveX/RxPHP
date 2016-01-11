<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class TakeUntilOperator implements OperatorInterface
{
    /** @var ObservableInterface */
    private $other;

    public function __construct(ObservableInterface $other)
    {
        $this->other = $other;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {

        return new CompositeDisposable([
            $observable->subscribe($observer, $scheduler),
            $this->other->subscribe(
                new CallbackObserver(
                    [$observer, "onCompleted"],
                    [$observer, "onError"]
                ),
                $scheduler
            )
        ]);
    }
}
