<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class TakeWhileOperator implements OperatorInterface
{
    public function __construct(
        private readonly null|\Closure $predicate,
        private readonly bool $inclusive = false
    ) {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $onNext = function ($value) use ($observer): void {
            try {
                if (($this->predicate)($value)) {
                    $observer->onNext($value);
                } else {
                    if ($this->inclusive) {
                        $observer->onNext($value);
                    }
                    $observer->onCompleted();
                }
            } catch (\Throwable $e) {
                $observer->onError($e);
            }
        };

        $callbackObserver = new CallbackObserver(
            $onNext,
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()
        );

        return $observable->subscribe($callbackObserver);
    }
}
