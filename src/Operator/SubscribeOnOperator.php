<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\ScheduledDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

final class SubscribeOnOperator implements OperatorInterface
{
    private $scheduler;

    public function __construct(SchedulerInterface $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposable       = new SerialDisposable();
        $singleDisposable = new SingleAssignmentDisposable();
        $disposable->setDisposable($singleDisposable);

        $singleDisposable->setDisposable(
            $this->scheduler->schedule(function () use ($disposable, $observer, $observable) {
                $subscription = $observable->subscribe($observer);
                $disposable->setDisposable(new ScheduledDisposable($this->scheduler, $subscription));
            })
        );

        return $disposable;
    }
}
