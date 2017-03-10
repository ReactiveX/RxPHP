<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

final class RetryWhenOperator implements OperatorInterface
{
    private $notificationHandler;

    public function __construct(callable $notificationHandler)
    {
        $this->notificationHandler = $notificationHandler;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $errors           = new Subject();
        $disposable       = new CompositeDisposable();
        $sourceDisposable = new EmptyDisposable();
        $innerCompleted   = false;
        $sourceError      = false;

        try {
            $when = ($this->notificationHandler)($errors->asObservable());
        } catch (\Throwable $e) {
            $observer->onError($e);
            return new EmptyDisposable();
        }

        $subscribeToSource = function () use ($observer, $disposable, $observable, &$sourceError, $errors, &$sourceDisposable, &$innerCompleted) {
            $sourceError      = false;
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
            ));

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
        ));

        $disposable->add($whenDisposable);

        $subscribeToSource();

        return $disposable;
    }
}
