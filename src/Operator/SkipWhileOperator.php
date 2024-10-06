<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class SkipWhileOperator implements OperatorInterface
{
    private bool $isSkipping = true;

    public function __construct(private readonly null|\Closure $predicate)
    {
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $callbackObserver = new CallbackObserver(
            function ($value) use ($observer, $observable): void {
                try {

                    if ($this->isSkipping) {
                        $this->isSkipping = ($this->predicate)($value, $observable);
                    }

                    if (!$this->isSkipping) {
                        $observer->onNext($value);
                    }

                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            fn ($err) => $observer->onError($err),
            fn () => $observer->onCompleted()
        );

        return $observable->subscribe($callbackObserver);
    }
}
