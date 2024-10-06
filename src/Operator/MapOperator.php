<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class MapOperator implements OperatorInterface
{
    public function __construct(private $selector)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $disposed   = false;
        $disposable = new CompositeDisposable();

        $selectObserver = new CallbackObserver(
            function ($nextValue) use ($observer, &$disposed): void {

                $value = null;
                try {
                    $value = ($this->selector)($nextValue);
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
                $observer->onNext($value);
            },
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()
        );

        $disposable->add(new CallbackDisposable(function () use (&$disposed): void {
            $disposed = true;
        }));

        $disposable->add($observable->subscribe($selectObserver));

        return $disposable;
    }
}
