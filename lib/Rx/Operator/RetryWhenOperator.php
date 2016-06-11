<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;
use Rx\Subject\Subject;

class RetryWhenOperator implements OperatorInterface
{
    private $notificationHandler;

    /**
     * RetryWhenOperator constructor.
     *
     * @param callable $notificationHandler
     */
    public function __construct(callable $notificationHandler)
    {
        $this->notificationHandler = $notificationHandler;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        $errors           = new Subject();
        $disposable       = new CompositeDisposable();
        $sourceDisposable = new EmptyDisposable();
        $innerCompleted   = false;
        $sourceError      = false;

        try {
            $when = call_user_func($this->notificationHandler, $errors->asObservable());
        } catch (\Exception $e) {
            $observer->onError($e);
            return;
        }

        $subscribeToSource = function () use (
            $observer,
            $disposable,
            $observable,
            &$sourceError,
            $errors,
            &$sourceDisposable,
            $scheduler,
            &$innerCompleted
        ) {
            $sourceError = false;
            $sourceDisposable = $observable->subscribe(new CallbackObserver(
                [$observer, 'onNext'],
                function ($err) use (
                    &$sourceError,
                    $errors,
                    $disposable,
                    &$sourceDisposable,
                    &$innerCompleted,
                    $observer
                ) {
                    $sourceError = true;
                    $disposable->remove($sourceDisposable);
                    $sourceDisposable->dispose();

                    if ($innerCompleted) {
                        $observer->onCompleted();
                        return;
                    }
                    $errors->onNext($err);
                },
                [$observer, 'onCompleted']
            ), $scheduler);

            $disposable->add($sourceDisposable);
        };

        $whenDisposable = $when->subscribe(new CallbackObserver(
            function ($x) use ($subscribeToSource, &$sourceError) {
                if ($sourceError) {
                    $sourceError = false;
                    $subscribeToSource();
                }
            },
            [$observer, 'onError'],
            function () use (&$innerCompleted, &$sourceError, $observer) {
                $innerCompleted = true;
                if ($sourceError) {
                    $observer->onCompleted();
                }
            }
        ), $scheduler);

        $disposable->add($whenDisposable);

        $subscribeToSource();

        return $disposable;
    }
}
