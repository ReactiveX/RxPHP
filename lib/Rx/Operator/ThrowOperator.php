<?php


namespace Rx\Operator;


use Rx\Disposable\EmptyDisposable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class ThrowOperator implements OperatorInterface
{
    private $error;

    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * ThrowOperator constructor.
     * @param $error
     * @param SchedulerInterface $scheduler
     */
    public function __construct($error, $scheduler = null)
    {
        $this->error = $error;
        $this->scheduler = $scheduler ? $scheduler : new ImmediateScheduler();
    }


    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        $observer->onError($this->error);

        return new EmptyDisposable();
    }

}