<?php

namespace Rx\Operator;

use Rx\Disposable\ScheduledDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class SubscribeOnOperator implements OperatorInterface
{

    /** @var SchedulerInterface  */
    private $scheduler;

    public function __construct(SchedulerInterface $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $disposable       = new SerialDisposable();
        $singleDisposable = new SingleAssignmentDisposable();
        $disposable->setDisposable($singleDisposable);

        $singleDisposable->setDisposable(
            $this->scheduler->schedule(function () use ($disposable, $observer, $observable, $scheduler) {
                $subscription = $observable->subscribe($observer, $scheduler);
                $disposable->setDisposable(new ScheduledDisposable($this->scheduler, $subscription));
            })
        );

        return $disposable;
    }
}
