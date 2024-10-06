<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class CountOperator implements OperatorInterface
{
    public function __construct(
        private readonly null|\Closure $predicate = null,
        private int                    $count = 0
    ) {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $callbackObserver = new CallbackObserver(
            function ($x) use ($observer): void {
                if ($this->predicate === null) {
                    $this->count++;

                    return;
                }
                try {
                    $predicate = $this->predicate;
                    if ($predicate($x)) {
                        $this->count++;
                    }
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            fn ($err) => $observer->onError($err),
            function () use ($observer): void {
                $observer->onNext($this->count);
                $observer->onCompleted();
            }
        );

        return $observable->subscribe($callbackObserver);
    }
}
