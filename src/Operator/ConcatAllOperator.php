<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ConcatAllOperator implements OperatorInterface
{
    private array $buffer = [];
    private CompositeDisposable $disposable;
    private DisposableInterface $innerDisposable;
    private bool $startBuffering = false;
    private bool $sourceCompleted = false;
    private bool $innerCompleted = true;

    public function __construct()
    {
        $this->disposable      = new CompositeDisposable();
        $this->innerDisposable = new EmptyDisposable();
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $subscription = $observable->subscribe(new CallbackObserver(
            function (ObservableInterface $innerObservable) use ($observable, $observer): void {
                try {

                    if ($this->startBuffering === true) {
                        $this->buffer[] = $innerObservable;
                        return;
                    }

                    $onCompleted = function () use (&$subscribeToInner, $observer): void {

                        $this->disposable->remove($this->innerDisposable);
                        $this->innerDisposable->dispose();

                        $this->innerCompleted = true;

                        $obs = array_shift($this->buffer);

                        if ($this->buffer === []) {
                            $this->startBuffering = false;
                        }

                        if ($obs) {
                            $subscribeToInner($obs);
                        } elseif ($this->sourceCompleted === true) {
                            $observer->onCompleted();
                        }
                    };

                    $subscribeToInner = function ($observable) use ($observer, &$onCompleted): void {
                        $callbackObserver = new CallbackObserver(
                            fn ($x) => $observer->onNext($x),
                            fn ($err) => $observer->onError($err),
                            $onCompleted
                        );

                        $this->innerCompleted = false;
                        $this->startBuffering = true;

                        $this->innerDisposable = $observable->subscribe($callbackObserver);
                        $this->disposable->add($this->innerDisposable);
                    };

                    $subscribeToInner($innerObservable);

                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            fn ($err) => $observer->onError($err),
            function () use ($observer): void {
                $this->sourceCompleted = true;
                if ($this->innerCompleted === true) {
                    $observer->onCompleted();
                }
            }
        ));

        $this->disposable->add($subscription);

        return $this->disposable;
    }
}
