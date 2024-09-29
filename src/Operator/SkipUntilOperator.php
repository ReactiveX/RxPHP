<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class SkipUntilOperator implements OperatorInterface
{
    public function __construct(private ObservableInterface $other)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $isOpen = false;

        $otherDisposable = new EmptyDisposable();

        /** @var DisposableInterface $otherDisposable */
        $otherDisposable = $this->other->subscribe(new CallbackObserver(
            function ($x) use (&$isOpen, &$otherDisposable): void {
                $isOpen = true;
                $otherDisposable->dispose();
            },
            function ($e) use ($observer): void {
                $observer->onError($e);
            },
            function () use (&$otherDisposable): void {
                $otherDisposable->dispose();
            }
        ));

        $sourceDisposable = $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, &$isOpen): void {
                if ($isOpen) {
                    $observer->onNext($x);
                }
            },
            function ($e) use ($observer): void {
                $observer->onError($e);
            },
            function () use ($observer, &$isOpen): void {
                if ($isOpen) {
                    $observer->onCompleted();
                }
            }
        ));

        return new CompositeDisposable([$otherDisposable, $sourceDisposable]);

    }
}
