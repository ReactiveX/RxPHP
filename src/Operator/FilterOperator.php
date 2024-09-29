<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class FilterOperator implements OperatorInterface
{
    public function __construct(private readonly null|\Closure $predicate)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $selectObserver = new CallbackObserver(
            function ($nextValue) use ($observer): void {
                $shouldFire = false;
                try {
                    $shouldFire = ($this->predicate)($nextValue);
                } catch (\Throwable $e) {
                    $observer->onError($e);
                }

                if ($shouldFire) {
                    $observer->onNext($nextValue);
                }
            },
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()
        );

        return $observable->subscribe($selectObserver);
    }
}
