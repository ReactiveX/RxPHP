<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable\ErrorObservable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;

class TimeoutOperator implements OperatorInterface
{
    private $timeout;

    /** @var SchedulerInterface */
    private $scheduler;

    /** @var ObservableInterface */
    private $timeoutObservable;

    public function __construct(int $timeout, ObservableInterface $timeoutObservable = null, SchedulerInterface $scheduler = null)
    {
        $this->timeout           = $timeout;
        $this->scheduler         = $scheduler;
        $this->timeoutObservable = $timeoutObservable;

        if ($this->timeoutObservable === null) {
            $this->timeoutObservable = new ErrorObservable(new \Exception('timeout'));
        }
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $scheduler = $this->scheduler ?? Scheduler::getAsync();

        $disposable = new CompositeDisposable();

        $sourceDisposable = new EmptyDisposable();

        $doTimeout = function () use ($observer, $disposable, &$sourceDisposable) {
            $disposable->remove($sourceDisposable);
            $sourceDisposable->dispose();
            $disposable->add($this->timeoutObservable->subscribe($observer));
        };

        $doTimeoutDisposable = $scheduler->schedule($doTimeout, $this->timeout);
        $disposable->add($doTimeoutDisposable);

        $rescheduleTimeout = function () use ($disposable, &$doTimeoutDisposable, $scheduler, $doTimeout) {
            $disposable->remove($doTimeoutDisposable);
            $doTimeoutDisposable->dispose();

            $doTimeoutDisposable = $scheduler->schedule($doTimeout, $this->timeout);
            $disposable->add($doTimeoutDisposable);
        };

        $sourceDisposable = $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, $rescheduleTimeout) {
                $rescheduleTimeout();
                $observer->onNext($x);
            },
            function ($err) use ($observer, $rescheduleTimeout) {
                $rescheduleTimeout();
                $observer->onError($err);
            },
            function () use ($observer, $rescheduleTimeout) {
                $rescheduleTimeout();
                $observer->onCompleted();
            }
        ));

        $disposable->add($sourceDisposable);

        return $disposable;
    }
}
