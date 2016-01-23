<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class DelayOperator implements OperatorInterface
{
    private $delay;

    /** @var SchedulerInterface */
    private $scheduler;

    /**
     * DelayOperator constructor.
     * @param $delay
     * @param SchedulerInterface $scheduler
     */
    public function __construct($delay, SchedulerInterface $scheduler = null)
    {
        $this->delay     = $delay;
        $this->scheduler = $scheduler;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        if ($this->scheduler !== null) {
            $scheduler = $this->scheduler;
        }
        if ($scheduler === null) {
            throw new \Exception("You must use a scheduler that support non-zero delay.");
        }

        $lastScheduledTime = 0;

        $disposable = new CompositeDisposable();

        $sourceDisposable = $observable->subscribe(new CallbackObserver(
            function ($x) use ($scheduler, $observer, &$lastScheduledTime, $disposable) {
                $schedDisp = $scheduler->schedule(function () use ($x, $observer, &$schedDisp, $disposable) {
                    $observer->onNext($x);
                    $disposable->remove($schedDisp);
                }, $this->delay);

                $disposable->add($schedDisp);
            },
            function ($err) use ($scheduler, $observer, &$lastScheduledTime, $disposable, &$sourceDisposable) {
                $disposable->remove($sourceDisposable);
                $sourceDisposable->dispose();
                $observer->onError($err);
            },
            function () use ($scheduler, $observer, $disposable, &$sourceDisposable) {
                $disposable->remove($sourceDisposable);
                $sourceDisposable->dispose();
                $schedDisp = $scheduler->schedule(function () use ($observer, &$schedDisp, $disposable) {
                    $observer->onCompleted();
                    $disposable->remove($schedDisp);
                }, $this->delay);

                $disposable->add($schedDisp);
            }
        ));

        $disposable->add($sourceDisposable);

        return $disposable;
    }
}