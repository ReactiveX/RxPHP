<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\Observable\ErrorObservable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\AsyncSchedulerInterface;
use Rx\Exception\TimeoutException;

final class TimeoutOperator implements OperatorInterface
{
    public function __construct(
        private readonly int                     $timeout,
        private readonly AsyncSchedulerInterface $scheduler,
        private null|ObservableInterface         $timeoutObservable = null
    ) {

        if (!$this->timeoutObservable instanceof \Rx\ObservableInterface) {
            $this->timeoutObservable = new ErrorObservable(new TimeoutException('timeout'), $scheduler);
        }
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposable = new CompositeDisposable();

        $sourceDisposable = new EmptyDisposable();

        $doTimeout = function () use ($observer, $disposable, &$sourceDisposable): void {
            $disposable->remove($sourceDisposable);
            $sourceDisposable->dispose();
            $disposable->add($this->timeoutObservable->subscribe($observer));
        };

        $doTimeoutDisposable = $this->scheduler->schedule($doTimeout, $this->timeout);
        $disposable->add($doTimeoutDisposable);

        $rescheduleTimeout = function () use ($disposable, &$doTimeoutDisposable, $doTimeout): void {
            $disposable->remove($doTimeoutDisposable);
            $doTimeoutDisposable->dispose();

            $doTimeoutDisposable = $this->scheduler->schedule($doTimeout, $this->timeout);
            $disposable->add($doTimeoutDisposable);
        };

        $sourceDisposable = $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, $rescheduleTimeout): void {
                $rescheduleTimeout();
                $observer->onNext($x);
            },
            function ($err) use ($observer, $rescheduleTimeout): void {
                $rescheduleTimeout();
                $observer->onError($err);
            },
            function () use ($observer, $rescheduleTimeout): void {
                $rescheduleTimeout();
                $observer->onCompleted();
            }
        ));

        $disposable->add($sourceDisposable);

        return $disposable;
    }
}
